<?php

declare(strict_types=1);

namespace App\Db;

/**
 * The search, sort and page of a DataTables list request, as SQL with bound
 * values. $columns lists the SQL of each column the table shows, in order.
 * Only these columns reach the SQL. The request supplies indexes and words.
 */
final class DataTables
{
    /**
     * The search as SQL to AND onto a WHERE clause, and its values. Each word
     * of sSearch must match at least one column. Each sSearch_<i> of a column
     * marked bSearchable_<i> must match that column. '' when there is no search.
     *
     * @param list<string> $columns
     * @return array{0: string, 1: list<string>}
     */
    public static function search(array $columns, mixed $parameters): array
    {
        $conditions = [];
        $values = [];

        $words = (string) ($parameters['sSearch'] ?? '');
        if ($words !== '' && $columns !== []) {
            foreach (explode(' ', $words) as $word) {
                $anyColumn = [];
                foreach ($columns as $column) {
                    $anyColumn[] = $column . ' LIKE ?';
                    $values[] = '%' . $word . '%';
                }
                $conditions[] = '(' . implode(' OR ', $anyColumn) . ')';
            }
        }

        foreach (array_values($columns) as $i => $column) {
            $filter = (string) ($parameters['sSearch_' . $i] ?? '');
            if (($parameters['bSearchable_' . $i] ?? '') == 'true' && $filter !== '') {
                $conditions[] = $column . ' LIKE ?';
                $values[] = '%' . $filter . '%';
            }
        }

        return [implode(' AND ', $conditions), $values];
    }

    /**
     * The sort as an ORDER BY list, such as "`role_name` DESC, `status` ASC".
     * A column index the list does not have, or one not marked bSortable, is
     * skipped. The direction is DESC only when sSortDir says so, else ASC.
     * '' when there is no sort.
     *
     * @param list<string> $columns
     */
    public static function order(array $columns, mixed $parameters): string
    {
        $order = [];
        $count = (int) ($parameters['iSortingCols'] ?? 0);
        for ($i = 0; $i < $count; $i++) {
            $index = (int) ($parameters['iSortCol_' . $i] ?? -1);
            if (!isset($columns[$index]) || ($parameters['bSortable_' . $index] ?? '') != 'true') {
                continue;
            }
            $direction = strtoupper(trim((string) ($parameters['sSortDir_' . $i] ?? ''))) === 'DESC' ? 'DESC' : 'ASC';
            $order[] = self::quoteColumn($columns[$index]) . ' ' . $direction;
        }
        return implode(', ', $order);
    }

    /** " LIMIT <length> OFFSET <start>", or '' when the request asks for every row. */
    public static function limit(mixed $parameters): string
    {
        if (!isset($parameters['iDisplayStart']) || ($parameters['iDisplayLength'] ?? '-1') == '-1') {
            return '';
        }
        return ' LIMIT ' . max(0, (int) $parameters['iDisplayLength']) . ' OFFSET ' . max(0, (int) $parameters['iDisplayStart']);
    }

    /**
     * Quotes a column name such as "status" or "d.geo_name".
     * An expression such as "DATE_FORMAT(...)" is left as the code wrote it.
     */
    private static function quoteColumn(string $column): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $column) !== 1) {
            return $column;
        }
        return '`' . str_replace('.', '`.`', $column) . '`';
    }
}
