<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentTaskParser
{
    public const int MAX_TASKS = 1000;

    private const int MAX_TITLE_LENGTH = 200;

    private const int MAX_GROUP_LENGTH = 180;

    private const int MAX_DESCRIPTION_LENGTH = 4000;

    /** @return list<array{title: string, description: string, source_text: string, group: ?string, checklist: list<array{title: string, done: bool}>, position: int}> */
    public function parse(string $content): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r", "\t"], ["\n", "\n", '    '], trim($content)));
        $items = [];
        $current = null;
        $group = null;
        $headingTask = false;
        $baseIndent = null;
        $skipLevel = null;
        $fence = null;
        $frontmatter = false;
        $fallback = [];

        foreach ($lines as $lineNumber => $line) {
            if ($lineNumber === 0 && trim($line) === '---') {
                $frontmatter = true;

                continue;
            }
            if ($frontmatter) {
                if (in_array(trim($line), ['---', '...'], true)) {
                    $frontmatter = false;
                }

                continue;
            }
            if (preg_match('/^\s*(`{3,}|~{3,})/u', $line, $match)) {
                if ($fence === null) {
                    $fence = $match[1][0];
                } elseif ($match[1][0] === $fence) {
                    $fence = null;
                }
                if ($current !== null && $skipLevel === null) {
                    $this->append($items[$current], $line);
                }

                continue;
            }
            if ($fence !== null) {
                if ($current !== null && $skipLevel === null) {
                    $this->append($items[$current], $line);
                }

                continue;
            }
            $heading = preg_match('/^(#{1,6})\s+(.+?)\s*#*$/u', $line, $match) === 1;
            $phase = preg_match('/^\s*(?:\*\*)?((?:tahap|fase|phase)\s+\d+\s*[:.)\-–—]?\s*.+?)(?:\*\*)?$/iu', $line, $phaseMatch) === 1;
            if ($heading || $phase) {
                $level = $heading ? strlen($match[1]) : 2;
                $title = $this->cleanTitle($heading ? $match[2] : $phaseMatch[1]);
                if ($skipLevel !== null && $level > $skipLevel) {
                    continue;
                }
                $skipLevel = null;
                if ($this->isContextHeading($title)) {
                    $skipLevel = $level;
                    $current = null;

                    continue;
                }
                if ($current !== null && preg_match('/^(?:kriteria|acceptance|definition of done|catatan|notes?|detail|validasi|contoh|examples?)(?:\s|:|$)/iu', $title)) {
                    $this->append($items[$current], $line);
                    $headingTask = true;

                    continue;
                }
                $current = null;
                $baseIndent = null;
                $headingTask = false;
                if ($level === 1) {
                    $group = null;

                    continue;
                }
                $isPhase = preg_match('/^(?:tahap|fase|phase)\s+\d+/iu', $title) === 1;
                $isStep = preg_match('/^(?:langkah|step|tugas|task)\s+\d+/iu', $title) === 1;
                if ($isPhase || ($level === 2 && ! $isStep && $this->hasChildren($lines, $lineNumber, $level))) {
                    $group = $title;

                    continue;
                }
                $items[] = $this->draft($title, $group, $line);
                $current = array_key_last($items);
                $headingTask = true;

                continue;
            }
            if (trim($line) === '' && $current === null && $skipLevel === null) {
                $fallback[] = '';
            }
            if ($skipLevel !== null || trim($line) === '' || preg_match('/^\s*(?:---+|\*\*\*+|___+)\s*$/', $line)) {
                continue;
            }
            if (preg_match('/^\s*(?:\*\*)?((?:langkah|step|tugas|task)\s+\d+\s*[:.)\-–—]?\s*.+?)(?:\*\*)?$/iu', $line, $step)) {
                $items[] = $this->draft($this->cleanTitle($step[1]), $group, $line);
                $current = array_key_last($items);
                $headingTask = true;

                continue;
            }
            if (preg_match('/^(\s*)(?:[-*+]|\d+[.)]|\d+(?:\.\d+)+[.)]?)\s+(?:\[[ xX]\]\s*)?(.+)$/u', $line, $list)) {
                $indent = strlen($list[1]);
                $baseIndent = $baseIndent === null ? $indent : min($baseIndent, $indent);
                if ($current !== null && ($headingTask || $indent > $baseIndent)) {
                    $checkTitle = $this->cleanTitle($list[2]);
                    if ($checkTitle !== '') {
                        $items[$current]['checklist'][] = ['title' => $checkTitle, 'done' => false];
                    }
                    $this->append($items[$current], trim($line));
                } else {
                    $items[] = $this->draft($this->cleanTitle($list[2]), $group, trim($line));
                    $current = array_key_last($items);
                }
            } elseif ($current !== null) {
                $this->append($items[$current], trim($line));
            } else {
                $fallback[] = $line;
            }
        }
        if ($items === []) {
            foreach (preg_split('/\n\s*\n/u', implode("\n", $fallback)) as $paragraph) {
                $paragraph = trim($paragraph);
                if ($paragraph !== '') {
                    $item = $this->draft(strtok($paragraph, "\n"), null, $paragraph);
                    $item['description'] = $paragraph;
                    $items[] = $item;
                }
            }
        }
        if (count($items) > self::MAX_TASKS) {
            $this->reject('Dokumen menghasilkan lebih dari 1.000 tugas. Pecah dokumen menjadi bagian yang lebih kecil.');
        }
        $result = [];
        foreach ($items as $item) {
            if ($item['title'] === '') {
                continue;
            }
            if (count($item['checklist']) > 50 || collect($item['checklist'])->contains(fn (array $check) => mb_strlen($check['title']) > 500)) {
                $this->reject('Satu tugas memiliki checklist terlalu panjang. Gunakan maksimal 50 poin, masing-masing 500 karakter.');
            }
            $key = Str::lower(($item['group'] ?? '').'|'.$item['title']);
            if (! isset($result[$key])) {
                $item['description'] = trim($item['description']);
                $item['position'] = count($result) + 1;
                $result[$key] = $item;
            } else {
                $existing = &$result[$key];
                if (trim($item['description']) !== '' && ! str_contains($existing['description'], trim($item['description']))) {
                    $existing['description'] .= "\n".trim($item['description']);
                    $existing['source_text'] .= "\n".$item['source_text'];
                }
                $existing['checklist'] = collect([...$existing['checklist'], ...$item['checklist']])->unique('title')->values()->all();
                if (count($existing['checklist']) > 50) {
                    $this->reject('Poin dengan judul sama memiliki terlalu banyak checklist. Pisahkan menjadi beberapa tugas.');
                }
                unset($existing);
            }
        }
        if ($result === []) {
            $this->reject('Belum ada langkah kerja yang dapat dibaca. Tambahkan judul tugas atau daftar kebutuhan ke dokumen.');
        }

        $expanded = [];
        foreach ($result as $item) {
            $sourceText = $item['source_text'];
            $fullTitle = $item['title'];
            $fullGroup = $item['group'];
            $item['title'] = $this->limitLabel($fullTitle, self::MAX_TITLE_LENGTH);
            $item['group'] = $fullGroup === null ? null : $this->limitLabel($fullGroup, self::MAX_GROUP_LENGTH);
            if ($item['title'] !== $fullTitle) {
                $sourceText .= "\nJudul lengkap: ".$fullTitle;
            }
            if ($item['group'] !== $fullGroup) {
                $sourceText .= "\nKelompok lengkap: ".$fullGroup;
            }
            $item['source_text'] = $sourceText;

            foreach ($this->splitLongText(trim($item['description']), self::MAX_DESCRIPTION_LENGTH) as $index => $description) {
                $draft = $item;
                $draft['description'] = trim($description);
                if ($index > 0) {
                    $draft['title'] = $this->appendTitleSuffix($item['title'], ' (lanjutan '.($index + 1).')');
                    $draft['source_text'] = 'Lanjutan uraian untuk "'.$item['title'].'": '.trim($description);
                    $draft['checklist'] = [];
                }
                $draft['position'] = count($expanded) + 1;
                $expanded[] = $draft;
            }
        }

        if (count($expanded) > self::MAX_TASKS) {
            $this->reject('Dokumen terlalu panjang setelah uraian tugas dipecah. Kurangi isi dokumen atau unggah dalam beberapa bagian.');
        }

        return $expanded;
    }

    /** @param list<string> $lines */
    private function hasChildren(array $lines, int $offset, int $level): bool
    {
        $fence = false;
        foreach (array_slice($lines, $offset + 1) as $line) {
            if (preg_match('/^\s*(```|~~~)/', $line)) {
                $fence = ! $fence;

                continue;
            }
            if ($fence) {
                continue;
            }
            if (preg_match('/^(#{1,6})\s+/', $line, $heading)) {
                return strlen($heading[1]) > $level;
            }
            if (preg_match('/^\s*(?:[-*+]|\d+[.)])\s+/', $line)) {
                return true;
            }
        }

        return false;
    }

    private function cleanTitle(string $title): string
    {
        return trim(str_replace(['**', '__', '`'], '', $title), " *_\t");
    }

    private function isContextHeading(string $title): bool
    {
        return preg_match('/^(?:(?:\d+[.)]\s*)?)(?:latar belakang|background|tujuan|goals?|objectives?|ringkasan|overview|konteks|context|non[- ]?goals?|di luar cakupan|out of scope|referensi|references?|glosarium|glossary|daftar isi|table of contents)(?:\s|:|$)/iu', $title) === 1;
    }

    /** @return array{title: string, description: string, source_text: string, group: ?string, checklist: array, position: int} */
    private function draft(string $title, ?string $group, string $source): array
    {
        return ['title' => $title, 'description' => $group ? 'Bagian: '.$group : '', 'source_text' => trim($source), 'group' => $group, 'checklist' => [], 'position' => 0];
    }

    /** @param array{description: string, source_text: string} $item */
    private function append(array &$item, string $line): void
    {
        $item['description'] .= "\n".$line;
        $item['source_text'] .= "\n".$line;
    }

    private function limitLabel(string $value, int $limit): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 1)).'…';
    }

    private function appendTitleSuffix(string $title, string $suffix): string
    {
        $prefixLength = self::MAX_TITLE_LENGTH - mb_strlen($suffix);

        return rtrim(mb_substr($title, 0, $prefixLength)).$suffix;
    }

    /** @return list<string> */
    private function splitLongText(string $text, int $limit): array
    {
        if (mb_strlen($text) <= $limit) {
            return [$text];
        }

        $chunks = [];
        while (mb_strlen($text) > $limit) {
            $candidate = mb_substr($text, 0, $limit);
            $breakPosition = mb_strrpos($candidate, "\n", 0, 'UTF-8');
            if ($breakPosition === false || $breakPosition < (int) ($limit / 2)) {
                $breakPosition = mb_strrpos($candidate, ' ', 0, 'UTF-8');
            }
            $chunkLength = $breakPosition !== false && $breakPosition >= (int) ($limit / 2)
                ? $breakPosition + 1
                : $limit;
            $chunks[] = mb_substr($text, 0, $chunkLength);
            $text = mb_substr($text, $chunkLength);
        }
        $chunks[] = $text;

        return $chunks;
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['content_markdown' => $message]);
    }
}
