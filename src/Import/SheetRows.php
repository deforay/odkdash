<?php

declare(strict_types=1);

namespace App\Import;

use DateInterval;
use DateTimeInterface;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Common\Helper\EncodingHelper;
use OpenSpout\Reader\CSV;
use OpenSpout\Reader\ODS;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX;

/**
 * The rows of an uploaded .xlsx, .ods or .csv file, read one at a time.
 *
 * Only the first sheet is read. Empty rows are kept, so row numbers match
 * the file. Each value is text or null: a number as PHP prints it, a date
 * as "Y-m-d H:i:s", a duration as "H:i:s", a formula as its saved result,
 * an empty cell as null.
 * A number shows without its format, so 123 shown as 00123 reads as 123.
 */
final class SheetRows
{
    /** The file types an upload may be. */
    public const array EXTENSIONS = ['xlsx', 'ods', 'csv'];

    /**
     * @return \Generator<int, list<string|null>>
     */
    public static function read(string $path, string $extension): \Generator
    {
        $extension = strtolower($extension);
        $copy = $extension === 'csv' ? self::utf8Copy($path) : null;
        $source = $copy ?? $path;
        $reader = self::reader($extension, $source);
        $reader->open($source);
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    // A formula reads as the result the file saved for it. A CSV
                    // saves none, so its "=1+2" reads as that text.
                    yield array_map(
                        static fn(Cell $cell): ?string => self::text(
                            $cell instanceof FormulaCell ? ($cell->getComputedValue() ?? $cell->getValue()) : $cell->getValue()
                        ),
                        $row->cells,
                    );
                }
                break;
            }
        } finally {
            $reader->close();
            if ($copy !== null) {
                unlink($copy);
            }
        }
    }

    private static function reader(string $extension, string $path): ReaderInterface
    {
        return match ($extension) {
            'xlsx' => new XLSX\Reader(new XLSX\Options(SHOULD_PRESERVE_EMPTY_ROWS: true)),
            'ods' => new ODS\Reader(new ODS\Options(SHOULD_PRESERVE_EMPTY_ROWS: true)),
            'csv' => new CSV\Reader(new CSV\Options(SHOULD_PRESERVE_EMPTY_ROWS: true, FIELD_DELIMITER: self::separator($path))),
            default => throw new \InvalidArgumentException(sprintf('Cannot read a .%s file. Upload an .xlsx, .ods or .csv file.', $extension)),
        };
    }

    /**
     * A UTF-8 copy of a CSV whose byte order mark names UTF-16 or UTF-32, or
     * null for any other CSV, which is read as UTF-8. OpenSpout splits a line
     * into fields before it converts the encoding, so it cannot read these
     * files itself.
     */
    private static function utf8Copy(string $path): ?string
    {
        $boms = [
            EncodingHelper::BOM_UTF32_LE => EncodingHelper::ENCODING_UTF32_LE,
            EncodingHelper::BOM_UTF32_BE => EncodingHelper::ENCODING_UTF32_BE,
            EncodingHelper::BOM_UTF16_LE => EncodingHelper::ENCODING_UTF16_LE,
            EncodingHelper::BOM_UTF16_BE => EncodingHelper::ENCODING_UTF16_BE,
        ];
        $head = (string) file_get_contents($path, false, null, 0, 4);
        foreach ($boms as $bom => $encoding) {
            if (str_starts_with($head, $bom)) {
                $copy = tempnam(sys_get_temp_dir(), 'csv');
                $text = substr((string) file_get_contents($path), strlen($bom));
                file_put_contents($copy, mb_convert_encoding($text, 'UTF-8', $encoding));
                return $copy;
            }
        }
        return null;
    }

    /**
     * Whichever of comma, semicolon and tab the first line has most of,
     * outside quotes. A comma when it has none. Excel saves a CSV with
     * semicolons in locales that write decimals with a comma.
     */
    private static function separator(string $path): string
    {
        $sample = (string) file_get_contents($path, false, null, 0, 65536);
        $firstLine = (string) preg_replace('/"[^"]*"/', '', strtok($sample, "\r\n") ?: '');
        $best = ',';
        foreach ([';', "\t"] as $separator) {
            if (substr_count($firstLine, $separator) > substr_count($firstLine, $best)) {
                $best = $separator;
            }
        }
        return $best;
    }

    private static function text(mixed $value): ?string
    {
        return match (true) {
            $value === null, $value === '' => null,
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            // A duration, such as 26:30:00, in hours past a day.
            $value instanceof DateInterval => sprintf('%02d:%02d:%02d', (int) $value->days * 24 + $value->h, $value->i, $value->s),
            is_bool($value) => $value ? 'TRUE' : 'FALSE',
            default => (string) $value,
        };
    }
}
