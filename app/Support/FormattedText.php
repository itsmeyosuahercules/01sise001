<?php

namespace App\Support;

class FormattedText
{
    public static function html(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));

        if ($text === '') {
            return '';
        }

        $blocks = preg_split("/\n{2,}/", $text) ?: [];
        $html = [];

        foreach ($blocks as $block) {
            $lines = explode("\n", $block);
            $items = self::listItems($lines);

            if ($items !== null) {
                $html[] = '<ul>'.implode('', array_map(
                    fn (string $item): string => '<li>'.self::inline($item).'</li>',
                    $items,
                )).'</ul>';

                continue;
            }

            $html[] = '<p>'.implode('<br>', array_map(
                fn (string $line): string => self::inline($line),
                $lines,
            )).'</p>';
        }

        return implode('', $html);
    }

    public static function toWhatsapp(string $text): string
    {
        $text = preg_replace('/\*\*([^*\n]+)\*\*/', '*$1*', $text) ?? $text;

        return preg_replace(
            '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/',
            '$1 ($2)',
            $text,
        ) ?? $text;
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>|null
     */
    private static function listItems(array $lines): ?array
    {
        $items = [];

        foreach ($lines as $line) {
            if (! preg_match('/^(?:-|\d+\.)\s+(.+)$/', $line, $matches)) {
                return null;
            }

            $items[] = $matches[1];
        }

        return $items === [] ? null : $items;
    }

    private static function inline(string $line): string
    {
        $escaped = self::escape($line);
        $links = [];

        $withPlaceholders = preg_replace_callback(
            '/\[([^\]\n]+)\]\((https?:\/\/[^\s)]+)\)/',
            function (array $matches) use (&$links): string {
                $anchor = self::anchor($matches[2], self::emphasis($matches[1]));

                if ($anchor === null) {
                    return $matches[0];
                }

                $links[] = $anchor;

                return '@@L'.(count($links) - 1).'@@';
            },
            $escaped,
        ) ?? $escaped;

        $withPlaceholders = preg_replace_callback(
            '/https?:\/\/[^\s<]+/',
            function (array $matches) use (&$links): string {
                $raw = $matches[0];
                $trailing = '';

                if (preg_match('/^(.*?)([.,;:!?]+)$/', $raw, $parts)) {
                    $raw = $parts[1];
                    $trailing = $parts[2];
                }

                $anchor = self::anchor($raw, $raw);

                if ($anchor === null) {
                    return $matches[0];
                }

                $links[] = $anchor.$trailing;

                return '@@L'.(count($links) - 1).'@@';
            },
            $withPlaceholders,
        ) ?? $withPlaceholders;

        $withPlaceholders = self::emphasis($withPlaceholders);

        foreach ($links as $index => $anchor) {
            $withPlaceholders = str_replace('@@L'.$index.'@@', $anchor ?? '', $withPlaceholders);
        }

        return $withPlaceholders;
    }

    private static function emphasis(string $escaped): string
    {
        $escaped = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*([^*\n]+)\*/', '<strong>$1</strong>', $escaped) ?? $escaped;

        return preg_replace('/_([^_\n]+)_/', '<em>$1</em>', $escaped) ?? $escaped;
    }

    private static function anchor(string $url, string $labelHtml): ?string
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = trim($url);

        if (preg_match('/[\s"\'<>]/', $url) === 1 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        if (! preg_match('/\Ahttps?:\/\//i', $url)) {
            return null;
        }

        return '<a href="'.self::escape($url).'" target="_blank" rel="noopener noreferrer">'.$labelHtml.'</a>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }
}
