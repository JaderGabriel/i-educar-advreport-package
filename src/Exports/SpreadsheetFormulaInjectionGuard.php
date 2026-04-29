<?php

namespace iEducar\Packages\AdvancedReports\Exports;

/**
 * Reduz risco de injeção de fórmula (CSV/Excel) ao abrir planilhas com dados não confiáveis (BD / usuários).
 *
 * @see https://owasp.org/www-community/attacks/CSV_Injection
 */
final class SpreadsheetFormulaInjectionGuard
{
    public static function sanitizeScalar(mixed $value): mixed
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }
        if ($value === '') {
            return '';
        }
        $first = $value[0];
        if ($first === '=' || $first === '+' || $first === '-' || $first === '@' || $first === "\t" || $first === "\r") {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<int, mixed>
     */
    public static function sanitizeRow(array $row): array
    {
        $out = [];
        foreach ($row as $k => $cell) {
            $out[$k] = is_array($cell) ? $cell : self::sanitizeScalar($cell);
        }

        return $out;
    }

    /**
     * @param array<int, string> $headings
     * @return array<int, string>
     */
    public static function sanitizeHeadings(array $headings): array
    {
        return array_map(static fn ($h) => (string) self::sanitizeScalar($h), $headings);
    }
}
