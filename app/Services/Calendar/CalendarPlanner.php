<?php

namespace App\Services\Calendar;

use App\Models\CalendarEvent;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class CalendarPlanner
{
    /**
     * @param  Collection<int, CalendarEvent>  $events
     * @return list<array{event: CalendarEvent, original_start: CarbonImmutable, starts_at: CarbonImmutable, ends_at: CarbonImmutable, status: string}>
     */
    public function occurrences(Collection $events, CarbonImmutable $from, CarbonImmutable $to, string $timezone): array
    {
        $occurrences = [];
        $localFrom = $from->setTimezone($timezone);
        $localTo = $to->setTimezone($timezone);

        foreach ($events as $event) {
            if (! $event->starts_at || ! $event->ends_at) {
                continue;
            }

            $baseStart = $event->starts_at->setTimezone($timezone);
            $baseEnd = $event->ends_at->setTimezone($timezone);
            $durationSeconds = max(0, $baseEnd->getTimestamp() - $baseStart->getTimestamp());
            $rule = $event->recurrence_rule;
            $candidateDates = $rule ? $this->recurrenceDates($baseStart, $localFrom, $localTo, $rule) : [$baseStart->startOfDay()];
            $exceptions = $event->exceptions->keyBy(fn ($exception) => $exception->original_starts_at->utc()->format('Y-m-d H:i:s'));

            foreach ($candidateDates as $date) {
                $originalStart = $date->setTime($baseStart->hour, $baseStart->minute, $baseStart->second);
                if (! $rule && ($originalStart > $localTo || $originalStart->addSeconds($durationSeconds) < $localFrom)) {
                    continue;
                }
                if ($rule && isset($rule['until']) && $date->toDateString() > $rule['until']) {
                    continue;
                }

                $exception = $exceptions->get($originalStart->utc()->format('Y-m-d H:i:s'));
                if ($exception?->action === 'cancelled') {
                    continue;
                }

                $startsAt = $exception?->new_starts_at?->setTimezone($timezone) ?? $originalStart;
                $endsAt = $exception?->new_ends_at?->setTimezone($timezone) ?? $startsAt->addSeconds($durationSeconds);
                if ($startsAt <= $localTo && $endsAt >= $localFrom) {
                    $occurrences[] = [
                        'event' => $event,
                        'original_start' => $originalStart,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'status' => $exception?->action === 'completed' ? 'completed' : $event->status,
                    ];
                }
            }
        }

        usort($occurrences, fn (array $left, array $right): int => $left['starts_at'] <=> $right['starts_at']);

        return $occurrences;
    }

    /** @return list<CarbonImmutable> */
    private function recurrenceDates(CarbonImmutable $baseStart, CarbonImmutable $from, CarbonImmutable $to, array $rule): array
    {
        $frequency = $rule['frequency'] ?? 'weekly';
        $interval = max(1, (int) ($rule['interval'] ?? 1));
        $startDate = $baseStart->startOfDay();
        $fromDate = $from->startOfDay()->max($startDate);
        $toDate = $to->endOfDay();
        $dates = [];

        if ($frequency === 'daily') {
            $daysFromStart = (int) $startDate->diffInDays($fromDate, false);
            $offset = max(0, (int) ceil($daysFromStart / $interval) * $interval);
            for ($date = $startDate->addDays($offset); $date <= $toDate; $date = $date->addDays($interval)) {
                $dates[] = $date;
            }
        } elseif ($frequency === 'monthly') {
            $monthsFromStart = (($fromDate->year - $startDate->year) * 12) + $fromDate->month - $startDate->month;
            $offset = max(0, (int) ceil($monthsFromStart / $interval) * $interval);
            for ($date = $startDate->addMonthsNoOverflow($offset); $date <= $toDate; $date = $date->addMonthsNoOverflow($interval)) {
                $dates[] = $date;
            }
        } else {
            $days = array_map('intval', $rule['days'] ?? [$startDate->dayOfWeekIso]);
            for ($date = $fromDate; $date <= $toDate; $date = $date->addDay()) {
                $dayOffset = (int) $startDate->diffInDays($date, false);
                $weekOffset = intdiv(max(0, (int) $startDate->startOfWeek()->diffInDays($date->startOfWeek(), false)), 7);
                if ($dayOffset >= 0 && $weekOffset % $interval === 0 && in_array($date->dayOfWeekIso, $days, true)) {
                    $dates[] = $date;
                }
            }
        }

        return $dates;
    }
}
