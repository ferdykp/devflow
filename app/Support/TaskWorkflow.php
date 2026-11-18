<?php

namespace App\Support;

class TaskWorkflow
{
    /**
     * @return array<string, array{label: string, drop_status: string, statuses: list<string>}>
     */
    public static function boardColumns(): array
    {
        return [
            'not_started' => [
                'label' => 'Belum dikerjakan',
                'drop_status' => 'todo',
                'statuses' => ['backlog', 'todo'],
            ],
            'in_progress' => [
                'label' => 'Sedang dikerjakan',
                'drop_status' => 'in_progress',
                'statuses' => ['in_progress', 'in_review', 'blocked'],
            ],
            'done' => [
                'label' => 'Selesai',
                'drop_status' => 'done',
                'statuses' => ['done'],
            ],
        ];
    }

    /** @return array<string, array<string, string>> */
    public static function groupedStatuses(): array
    {
        return [
            'Belum dikerjakan' => [
                'backlog' => UiLabel::status('backlog'),
                'todo' => UiLabel::status('todo'),
            ],
            'Sedang dikerjakan' => [
                'in_progress' => UiLabel::status('in_progress'),
                'in_review' => UiLabel::status('in_review'),
                'blocked' => UiLabel::status('blocked'),
            ],
            'Selesai' => [
                'done' => UiLabel::status('done'),
            ],
        ];
    }

    /** @return array<string, string> */
    public static function flatStatuses(): array
    {
        $statuses = [];
        foreach (self::groupedStatuses() as $group) {
            $statuses += $group;
        }

        return $statuses;
    }
}
