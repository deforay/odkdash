<?php

declare(strict_types=1);

namespace App\View;

use RuntimeException;

/**
 * The escapeHtml and escapeHtmlAttr view helpers.
 *
 * html() is htmlspecialchars() with quotes escaped. attr() follows the
 * OWASP rule for attribute values: every character except letters,
 * digits and ",.-_" becomes an entity, so the value stays safe even in an
 * unquoted attribute.
 */
final class Escape
{
    /** The characters attr() writes as named entities. */
    private const array NAMED = [34 => 'quot', 38 => 'amp', 60 => 'lt', 62 => 'gt'];

    public static function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function attr(string $value): string
    {
        if ($value === '' || ctype_digit($value)) {
            return $value;
        }
        if (!mb_check_encoding($value, 'UTF-8')) {
            throw new RuntimeException('String to be escaped was not valid UTF-8.');
        }

        return (string) preg_replace_callback('/[^a-z0-9,\.\-_]/iSu', self::attrChar(...), $value);
    }

    /** @param array<int, string> $match */
    private static function attrChar(array $match): string
    {
        $char = $match[0];
        $byte = ord($char);

        // Control characters other than tab, line feed and carriage return
        // have no safe entity, so they become the replacement character.
        if (($byte <= 0x1f && !in_array($char, ["\t", "\n", "\r"], true)) || ($byte >= 0x7f && $byte <= 0x9f)) {
            return '&#xFFFD;';
        }

        $codePoint = mb_ord($char, 'UTF-8');
        if (isset(self::NAMED[$codePoint])) {
            return '&' . self::NAMED[$codePoint] . ';';
        }

        return $codePoint > 255 ? sprintf('&#x%04X;', $codePoint) : sprintf('&#x%02X;', $codePoint);
    }
}
