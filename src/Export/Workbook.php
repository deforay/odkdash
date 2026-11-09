<?php

declare(strict_types=1);

namespace App\Export;

use DateTimeImmutable;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\DateTimeCell;
use OpenSpout\Common\Entity\Cell\EmptyCell;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * An .xlsx file written one row at a time, so an export of every audit
 * holds one row in memory, not the whole sheet.
 *
 * Values are typed as the earlier writer typed them:
 *
 * - A string of digits is a number, with or without a decimal part. One or
 *   two decimals show as 0.00. A leading zero, as in a phone number, keeps
 *   it text.
 * - "85%" and "8.5%" are numbers shown as percentages.
 * - "2026-01-09" is a date.
 * - Any other string is text, so a value that starts with "=" is never a
 *   formula.
 * - A Cell is written as it is.
 *
 * Each column is as wide as its longest value plus one, up to 60, unless
 * the file was opened with a fixed width.
 */
final class Workbook
{
    private const int MAX_WIDTH = 60;

    private readonly Writer $writer;

    /** @var array<int, int> The longest value in each column, by column number from 1. */
    private array $longest = [];

    /**
     * @param list<string> $merge Ranges to merge, such as "A1:Q1".
     */
    public function __construct(string $path, array $merge = [], private readonly ?float $columnWidth = null)
    {
        $options = new Options();
        foreach ($merge as $range) {
            if (!preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $range, $m)) {
                throw new \InvalidArgumentException(sprintf('Not a cell range: "%s".', $range));
            }
            $options->mergeCells(self::columnIndex($m[1]), (int) $m[2], self::columnIndex($m[3]), (int) $m[4]);
        }
        $this->writer = new Writer($options);
        $this->writer->openToFile($path);
    }

    /**
     * @param array<mixed> $values One value per column, in order. Keys are ignored.
     */
    public function addRow(array $values, ?Style $style = null): void
    {
        $cells = [];
        $column = 1;
        foreach ($values as $value) {
            $cells[] = self::cell($value, $style);
            $length = $value instanceof Cell ? 0 : mb_strlen((string) $value);
            $this->longest[$column] = max($this->longest[$column] ?? 0, $length);
            $column++;
        }
        $this->writer->addRow(new Row($cells));
    }

    public function close(): void
    {
        $sheet = $this->writer->getCurrentSheet();
        foreach ($this->longest as $column => $length) {
            if ($this->columnWidth !== null) {
                $sheet->setColumnWidth($this->columnWidth, $column);
            } elseif ($length > 0) {
                $sheet->setColumnWidth(min($length + 1, self::MAX_WIDTH), $column);
            }
        }
        $this->writer->close();
    }

    public static function cell(mixed $value, ?Style $style = null): Cell
    {
        if ($value instanceof Cell) {
            return $value;
        }
        if ($value === null || $value === '') {
            return new EmptyCell(null, $style);
        }
        if (is_int($value) || is_float($value)) {
            return new NumericCell($value, $style);
        }
        $text = (string) $value;
        $format = static fn(string $code): Style => ($style ?? new Style())->withFormat($code);
        if (preg_match('/^-?(0|[1-9]\d{0,14})\z/', $text)) {
            // Over 10 digits, the General format would show 1.23E+11.
            return new NumericCell((int) $text, strlen($text) > 10 ? $format('0') : $style);
        }
        if (preg_match('/^-?(0|[1-9]\d{0,14})\.\d{1,2}\z/', $text)) {
            return new NumericCell((float) $text, $format('0.00'));
        }
        if (preg_match('/^-?(0|[1-9]\d{0,14})\.\d+\z/', $text)) {
            return new NumericCell((float) $text, $style);
        }
        if (preg_match('/^(-?\d+)%\z/', $text, $m)) {
            return new NumericCell(round((int) $m[1] / 100, 2), $format('0%'));
        }
        if (preg_match('/^(-?\d+\.\d+)%\z/', $text, $m)) {
            return new NumericCell(round((float) $m[1] / 100, 4), $format('0.00%'));
        }
        // "mm-dd-yy" is Excel's built-in short date, shown in the reader's own date order.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\z/', $text, $m) && $m[1] >= 1900 && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return new DateTimeCell(new DateTimeImmutable($text), $format('mm-dd-yy'));
        }
        return new StringCell($text, $style);
    }

    /** "A" is 0, "Z" is 25, "AA" is 26. */
    private static function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + ord($letter) - 64;
        }
        return $index - 1;
    }
}
