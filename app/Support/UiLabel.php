<?php

namespace App\Support;

final class UiLabel
{
    public static function status(?string $value): string
    {
        return match ($value) {
            'backlog' => 'Antrean tugas',
            'todo' => 'Belum dikerjakan',
            'in_progress' => 'Sedang dikerjakan',
            'in_review' => 'Dalam peninjauan',
            'blocked' => 'Terhambat',
            'done' => 'Selesai',
            'active' => 'Aktif',
            'paused' => 'Dijeda',
            'archived' => 'Diarsipkan',
            default => ucfirst(str_replace('_', ' ', (string) $value)),
        };
    }

    public static function priority(?string $value): string
    {
        return match ($value) {
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            default => ucfirst((string) $value),
        };
    }
}
