<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * An audit's percentage score as the SPI-RT and SPI-RRT checklists record
 * it: a whole number. Part C of each checklist sets the levels on whole
 * percentages: level 0 below 40, level 1 from 40 to 59, level 2 from 60 to
 * 79, level 3 from 80 to 89, level 4 from 90. A score such as 89.5 rounds
 * to 90, so it is level 4.
 *
 * Compare the rounded score with 40, 60, 80 and 90 wherever a level is
 * decided, in PHP with percent() and in SQL with sql(). Show the score as
 * stored.
 */
final class AuditScore
{
    /** The score rounded to a whole number, half up. A blank score is 0. */
    public static function percent(mixed $score): float
    {
        return round(is_numeric($score) ? (float) $score : 0.0);
    }

    /**
     * The same rounding in SQL. MySQL's ROUND() can round 88.5 down to 88
     * on a float, and the score column is text, so add 0.5 and floor it.
     */
    public static function sql(string $column): string
    {
        return "FLOOR($column + 0.5)";
    }
}
