<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentTaskParser
{
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
        if (count($items) > 50) {
            $this->reject('Dokumen menghasilkan lebih dari 50 tugas. Pecah dokumen menjadi bagian yang lebih kecil.');
        }
        $result = [];
        foreach ($items as $item) {
            if ($item['title'] === '') {
                continue;
            }
            if (mb_strlen($item['title']) > 200 || mb_strlen($item['description']) > 4000 || mb_strlen($item['group'] ?? '') > 180) {
                $this->reject('Satu bagian terlalu panjang. Gunakan judul maksimal 200 karakter, nama kelompok 180 karakter, dan uraian 4.000 karakter.');
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
                if (mb_strlen($existing['description']) > 4000 || count($existing['checklist']) > 50) {
                    $this->reject('Poin dengan judul sama terlalu panjang. Pisahkan menjadi beberapa tugas.');
                }
                unset($existing);
            }
        }
        if ($result === []) {
            $this->reject('Belum ada langkah kerja yang dapat dibaca. Tambahkan judul tugas atau daftar kebutuhan ke dokumen.');
        }

        return array_values($result);
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

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['content_markdown' => $message]);
    }
}
