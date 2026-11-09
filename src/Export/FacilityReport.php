<?php

declare(strict_types=1);

namespace App\Export;

use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderStyle;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;

/**
 * The layout of the facility reports: a title in row 1, a subtitle in
 * row 2, the column headings in rows 4 and 5, and one row per line of the
 * report from row 6. Every column is 20 wide.
 *
 * The headings are bold, centred and boxed with a thick border. Each data
 * cell is centred, wraps, and has a medium border.
 */
final class FacilityReport
{
    /**
     * @param list<string> $headings
     * @param iterable<list<mixed>> $rows
     */
    public static function write(string $path, string $title, string $subtitle, array $headings, iterable $rows): void
    {
        $merge = ['A1:B1', 'A2:B2'];
        foreach (array_keys($headings) as $index) {
            $column = chr(65 + $index);
            $merge[] = "{$column}4:{$column}5";
        }
        $book = new Workbook($path, $merge, 20);
        $book->addRow([$title], (new Style())->withFontBold(true)->withFontSize(16));
        $book->addRow([$subtitle], (new Style())->withFontBold(true)->withFontSize(13));
        $book->addRow([]);
        $heading = (new Style())
            ->withFontBold(true)
            ->withFontSize(12)
            ->withCellAlignment(CellAlignment::CENTER)
            ->withCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->withBorder(self::border(BorderWidth::THICK));
        $book->addRow($headings, $heading);
        // Empty text, not empty cells: OpenSpout skips a row of empty cells,
        // and the merged headings take their bottom border from this row.
        $book->addRow(array_fill(0, count($headings), new StringCell('', $heading)));
        $cell = (new Style())
            ->withCellAlignment(CellAlignment::CENTER)
            ->withShouldWrapText(true)
            ->withBorder(self::border(BorderWidth::MEDIUM));
        foreach ($rows as $row) {
            $book->addRow(array_map(
                static fn(mixed $value): mixed => is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value,
                $row,
            ), $cell);
        }
        $book->close();
    }

    private static function border(BorderWidth $width): Border
    {
        return new Border(
            new BorderPart(BorderName::TOP, width: $width, style: BorderStyle::SOLID),
            new BorderPart(BorderName::RIGHT, width: $width, style: BorderStyle::SOLID),
            new BorderPart(BorderName::BOTTOM, width: $width, style: BorderStyle::SOLID),
            new BorderPart(BorderName::LEFT, width: $width, style: BorderStyle::SOLID),
        );
    }
}
