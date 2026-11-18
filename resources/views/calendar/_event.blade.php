@php($event = $item['event'])
<article
    @if ($item['status'] === 'planned' && !$event->all_day) draggable="true" data-calendar-event-id="{{ $event->id }}" data-calendar-event-title="{{ $event->title }}" data-calendar-event-description="{{ $event->description }}" data-calendar-event-start="{{ $item['starts_at']->format('Y-m-d\TH:i') }}" data-calendar-event-end="{{ $item['ends_at']->format('Y-m-d\TH:i') }}" data-calendar-event-timezone="{{ $timezone }}" data-calendar-event-update-url="{{ route('calendar.events.update', $event) }}" data-calendar-event-reschedule-url="{{ route('calendar.events.reschedule', $event) }}" @if ($event->recurrence_rule)data-calendar-event-recurring="true" data-calendar-event-original-start="{{ $item['original_start']->utc()->format('Y-m-d H:i:s') }}" @endif
    @endif
    class="m-2 rounded-xl border border-zinc-200 bg-white p-3 shadow-xs {{ $item['status'] === 'planned' && !$event->all_day ? 'cursor-grab active:cursor-grabbing' : '' }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2"><i class="fa-solid fa-grip-vertical text-zinc-300"
                    aria-hidden="true"></i>
                <h4 class="font-semibold">{{ $event->title }}</h4><span
                    class="rounded-full px-2 py-0.5 text-[11px] {{ $item['status'] === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">{{ $item['status'] === 'completed' ? 'Selesai' : 'Direncanakan' }}</span>
                @if ($event->is_flexible)
                    <span class="text-[11px] text-zinc-500">Waktu fleksibel</span>@endif @if ($event->recurrence_rule)<span class="text-[11px] text-zinc-500">Berulang
                            {{ $event->recurrence_rule['frequency'] }}</span>@endif
            </div>
            <p class="mt-1 text-xs text-zinc-500">
                {{ $event->all_day ? 'Sepanjang hari' : $item['starts_at']->format('H:i') . '–' . $item['ends_at']->format('H:i') }}
                @if ($event->timezone) · {{ $event->timezone }}
                @endif
            </p>
            @if ($event->description)
                <p class="mt-2 whitespace-pre-wrap text-sm text-zinc-600">{{ $event->description }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($item['status'] === 'planned')@unless ($event->all_day)
                <div class="flex items-center rounded-lg bg-zinc-50 p-0.5"><button type="button"
                        data-calendar-duration-adjust="-15"
                        class="grid size-9 place-items-center rounded-md text-zinc-600 transition hover:bg-white hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-700"
                        aria-label="Kurangi durasi 15 menit"><i class="fa-solid fa-minus text-[10px]"
                            aria-hidden="true"></i></button><button type="button" data-calendar-duration-adjust="15"
                        class="grid size-9 place-items-center rounded-md text-zinc-600 transition hover:bg-white hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-700"
                        aria-label="Tambah durasi 15 menit"><i class="fa-solid fa-plus text-[10px]"
                            aria-hidden="true"></i></button></div>
            @endunless
            <form method="POST" action="{{ route('calendar.events.complete', $event) }}">@csrf @if ($event->recurrence_rule)<input type="hidden" name="original_start"
                        value="{{ $item['original_start']->utc()->format('Y-m-d H:i:s') }}">@endif <button
                    class="min-h-10 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-emerald-700">Tandai
                    selesai</button></form>
        @endif
    </div>
</div>
@if ($event->task)
    <p class="mt-2 text-xs text-zinc-500">Tugas: {{ $event->task->title }}</p>@endif @if ($event->routine)
        <p class="mt-1 text-xs text-zinc-500">Rutinitas: {{ $event->routine->title }}</p>@endif @if ($event->lesson)
            <p class="mt-1 text-xs text-zinc-500">Belajar: {{ $event->lesson->title }}</p>
        @endif
        @if ($item['status'] === 'planned')
            <details class="mt-2 border-t border-zinc-100 pt-2">
                <summary
                    class="flex min-h-10 cursor-pointer items-center rounded-lg border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-700">
                    Ubah atau batalkan</summary>
                @if ($event->recurrence_rule)
                    <form method="POST" action="{{ route('calendar.events.reschedule', $event) }}"
                        class="mt-2 grid gap-3 sm:grid-cols-2">@csrf @method('PATCH')<input type="hidden"
                            name="original_start"
                            value="{{ $item['original_start']->utc()->format('Y-m-d H:i:s') }}"><input
                            type="hidden" name="timezone" value="{{ $timezone }}"><x-ui.field
                            label="Mulai kejadian ini" name="starts_at" type="datetime-local" :value="$item['starts_at']->format('Y-m-d\TH:i')"
                            required /><x-ui.field label="Selesai" name="ends_at" type="datetime-local"
                            :value="$item['ends_at']->format('Y-m-d\TH:i')" required />
                        <div class="sm:col-span-2"><x-ui.button variant="secondary">Jadwalkan ulang
                                kejadian</x-ui.button></div>
                    </form>
                    <form method="POST" action="{{ route('calendar.events.cancel', $event) }}" class="mt-2"
                        data-confirm="Batalkan kejadian ini saja?">@csrf @method('DELETE')<input type="hidden"
                            name="original_start"
                            value="{{ $item['original_start']->utc()->format('Y-m-d H:i:s') }}"><button
                            class="min-h-10 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-red-700">Batalkan
                            kejadian ini</button></form>
                    <form method="POST" action="{{ route('calendar.events.cancel', $event) }}"
                        data-confirm="Batalkan seluruh rangkaian agenda ini?">@csrf @method('DELETE')<button
                            class="min-h-10 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-red-700">Batalkan
                        seluruh rangkaian</button></form>@else<form method="POST"
                        action="{{ route('calendar.events.update', $event) }}"
                        class="mt-2 grid gap-3 sm:grid-cols-2">@csrf @method('PATCH')<input type="hidden"
                            name="timezone" value="{{ $timezone }}"><x-ui.field label="Judul" name="title"
                            :value="$event->title" required /><x-ui.field label="Mulai" name="starts_at"
                            type="datetime-local" :value="$item['starts_at']->format('Y-m-d\TH:i')" required /><x-ui.field label="Selesai"
                            name="ends_at" type="datetime-local" :value="$item['ends_at']->format('Y-m-d\TH:i')" required /><x-ui.textarea
                            label="Catatan" name="description" rows="2" :value="$event->description" />
                        <div class="sm:col-span-2"><x-ui.button variant="secondary">Simpan agenda</x-ui.button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('calendar.events.cancel', $event) }}" class="mt-2"
                        data-confirm="Batalkan agenda ini?">@csrf @method('DELETE')<button
                            class="min-h-10 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-red-700">Batalkan
                            agenda</button></form>
                @endif
            </details>
        @endif
</article>
