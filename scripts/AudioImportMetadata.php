<?php

use Illuminate\Support\Str;

final class PalmaAudioImportMetadata
{
    public static function normalize(?string $value): string
    {
        $value = Str::lower(Str::ascii(Str::squish(str_replace(['ª', 'º'], '', $value ?? ''))));

        return preg_replace('/^([123])\s*(?=[a-z])/', '$1 ', $value);
    }

    public static function titleKey(?string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', self::normalize(explode('|', $value ?? '')[0])));
    }

    private static function date(?string $value): ?string
    {
        if (preg_match('~\b(\d{1,2})/(\d{1,2})/(\d{4})\b~', $value ?? '', $matches)) {
            return checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])
                ? sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]) : null;
        }
        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $value ?? '', $matches)) {
            return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $matches[0] : null;
        }

        return null;
    }

    private static function reference(?string $value, array $books): array
    {
        $aliases = [];
        foreach ($books as $book) {
            $aliases[self::normalize($book)] = $book;
        }
        foreach (['salmo' => 'Salmos', 'psalm' => 'Salmos', 'psalms' => 'Salmos',
            'matthew' => 'Mateo', 'acts' => 'Hechos', 'ephesians' => 'Efesios',
            'f ilipenses' => 'Filipenses'] as $alias => $book) {
            if (in_array($book, $books, true)) {
                $aliases[$alias] = $book;
            }
        }
        uksort($aliases, fn ($a, $b) => strlen($b) <=> strlen($a));
        $text = self::normalize(str_replace(['–', '—'], '-', $value ?? ''));
        foreach ($aliases as $alias => $book) {
            if (preg_match('/^'.preg_quote($alias, '/').'(?:\s*(?=\d)|$)(.*)$/', $text, $matches)) {
                return [$book, trim($matches[1]) ?: null];
            }
        }

        return [null, null];
    }

    public static function build(array $row, array $books, array $override = []): array
    {
        $notes = [];
        $holds = [];
        if (! empty($override['provenance'])) {
            $notes[] = ['metadata_source' => $override['provenance']];
        }
        $parts = explode('|', $row['title'] ?? '');
        $title = Str::squish(strip_tags($parts[0]));
        $generic = $title === '' || preg_match('~^(?:\d{1,2}/\d{1,2}/\d{4}|\d{4}-\d{2}-\d{2})(?:\s+[MTE]\d*)?$~i', $title);
        $filenameDate = self::date($row['filenameDate'] ?? null);
        $encodedDate = self::date($row['encodedBy'] ?? null);
        $titleDate = self::date($row['title'] ?? null);
        $votes = array_count_values(array_filter([$filenameDate, $encodedDate, $titleDate]));
        arsort($votes);
        $date = array_key_first($votes);
        if (count($votes) > 1) {
            $notes[] = ['date_sources' => ['filename' => $filenameDate, 'encoded' => $encodedDate, 'title' => $titleDate], 'chosen' => $date];
            if (reset($votes) < 2 || ($filenameDate && $date !== $filenameDate)) {
                $holds[] = 'Fecha discrepante pendiente de confirmar';
                $date = null;
            }
        }
        if (array_key_exists('date', $override)) {
            $date = self::date($override['date']);
            $holds = [];
            $notes[] = 'Fecha confirmada por el usuario';
        }

        $path = self::normalize($row['path']);
        $genre = self::normalize($row['genre'] ?? null);
        $category = match ($genre) {
            'predicaciones', 'predicacion' => 'Predicaciones',
            'conferencias', 'conferencia' => 'Conferencias',
            'temas esenciales' => 'Temas Esenciales',
            default => str_contains($path, '/conferencias/') ? 'Conferencias'
                : (str_contains($path, '/temas-esenciales/') ? 'Temas Esenciales' : 'Predicaciones'),
        };
        $series = Str::squish($row['subtitle'] ?? '') ?: null;
        if (! $series && str_starts_with($path, 'serie apocalipsis')) {
            $series = 'Apocalipsis';
        }
        if ($series) {
            $series = str_replace('silgo XXI', 'siglo XXI', $series);
        }
        $author = Str::squish($row['artist'] ?? '') ?: null;
        $aliases = ['andres brich' => 'Andrés Birch', 'alfondo ramirez' => 'Alfonso Ramírez',
            'daniel fantelly' => 'Daniel Fantelli', 'j. antono alvarez' => 'J. Antonio Álvarez'];
        if (isset($aliases[self::normalize($author)])) {
            $notes[] = ['source_author' => $author, 'spelling' => $aliases[self::normalize($author)]];
            $author = $aliases[self::normalize($author)];
        }
        [$book, $citation] = self::reference($row['album'] ?? null, $books);
        if (! $book && isset($parts[1])) {
            [$book, $citation] = self::reference($parts[1], $books);
        }
        if (! $book && $series === 'Apocalipsis') {
            $book = 'Apocalipsis';
        }
        $turn = match (self::normalize(($row['conductor'] ?? '') ?: ($row['filenameTurn'] ?? ''))) {
            'm', 'manana' => 'Mañana',
            't', 'tarde', 'te' => 'Tarde',
            default => null,
        };
        if ($generic) {
            $dateLabel = $date ? (new DateTimeImmutable($date))->format('d/m/Y') : '';
            $title = ($series ?: ($category === 'Conferencias' ? 'Conferencia' : 'Predicación')).($dateLabel ? ' — '.$dateLabel : '');
            $notes[] = array_key_exists('title', $override)
                ? 'Título completado desde la fuente complementaria; el original no indica título del mensaje'
                : 'Título descriptivo basado en serie/categoría y fecha; el original no indica título del mensaje';
        }
        $values = ['title' => $title, 'author' => $author, 'series' => $series, 'category' => $category,
            'book' => $book, 'citation' => $citation, 'date' => $date, 'turn' => $turn];
        foreach (['title', 'author', 'series', 'category', 'book', 'citation', 'turn'] as $key) {
            if (array_key_exists($key, $override)) {
                $values[$key] = $override[$key];
            }
        }
        if (! $values['author']) {
            $holds[] = 'Autor pendiente de identificar';
        }
        if (! $values['date'] && ! in_array('Fecha discrepante pendiente de confirmar', $holds, true)) {
            $holds[] = 'Fecha pendiente de identificar';
        }
        if (! $values['title'] || mb_strlen($values['title']) > 255 || mb_strlen($values['citation'] ?? '') > 100) {
            throw new RuntimeException('Invalid title or citation length: '.$row['path']);
        }
        $seconds = (int) round($row['durationSeconds'] ?? 0);
        if ($seconds <= 0) {
            throw new RuntimeException('Missing audio duration: '.$row['path']);
        }
        $values['duration'] = $seconds >= 3600
            ? sprintf('%d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60)
            : sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
        $description = match ($values['category']) {
            'Conferencias' => 'Conferencia', 'Temas Esenciales' => 'Enseñanza', default => 'Predicación',
        };
        $description .= ' «'.$values['title'].'»';
        if ($values['author']) {
            $description .= ', de '.$values['author'];
        }
        $description .= '.';
        if ($values['book']) {
            $description .= ' Texto bíblico: '.$values['book'].($values['citation'] ? ' '.$values['citation'] : '').'.';
        }
        if ($values['series']) {
            $description .= ' Serie: '.$values['series'].'.';
        }
        $description .= ' Iglesia Palma'.($date ? ', '.(new DateTimeImmutable($date))->format('d/m/Y') : '').'.';
        if (str_contains($row['filename'], '_eng_')) {
            $description .= ' Versión en inglés.';
        }
        $values['description'] = $override['description'] ?? $description;
        $values['readyToPublish'] = $holds === [];
        $values['holds'] = $holds;
        $values['notes'] = $notes;

        return $values;
    }
}
