<section wire:ignore x-data="dailyReminder(@js($ayah), @js($reminder))" x-cloak
    x-show="{{ $compact ? 'ready && supported && !active' : 'true' }}"
    class="rounded-2xl border border-tilawa-line bg-tilawa-surface {{ $compact ? 'px-5 py-4' : 'p-6 md:p-8' }} shadow-sm" aria-labelledby="daily-reminder-title">
    <div class="flex items-center gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-tilawa-teal-light text-tilawa-teal-dark">
            <x-ui.icon name="bell" class="h-5 w-5" />
        </div>
        <div class="min-w-0 grow">
            <h2 id="daily-reminder-title" class="text-sm font-bold text-tilawa-ink">Ayah of the Day reminder</h2>
            <p class="mt-0.5 text-xs leading-5 text-tilawa-sub" x-show="!active">A notification every morning at 7:00 AM with the ayah of the day.</p>
            <p class="mt-0.5 flex items-center gap-1.5 text-xs font-semibold leading-5 text-tilawa-teal-dark dark:text-tilawa-teal-light" x-show="active" x-cloak>
                <x-ui.icon name="check" class="h-3.5 w-3.5" /> On &middot; every day at <span x-text="time"></span>
            </p>
        </div>
        <button type="button" x-show="supported && !active" @click="allow()" :disabled="busy || !ready"
            class="shrink-0 rounded-full bg-tilawa-amber px-5 py-2.5 text-sm font-bold text-[#12313a] transition hover:brightness-105 disabled:opacity-60">
            Allow
        </button>
        <button type="button" x-show="active" x-cloak @click="turnOff()" :disabled="busy"
            class="shrink-0 text-xs font-semibold text-tilawa-sub underline underline-offset-4 transition hover:text-tilawa-ink">
            Turn off
        </button>
    </div>
    @unless ($compact)
        <div x-show="active" x-cloak class="mt-4 flex items-center justify-between gap-4 border-t border-tilawa-line pt-4">
            <label for="daily-reminder-time" class="text-sm font-semibold text-tilawa-ink">Reminder time</label>
            <input id="daily-reminder-time" type="time" x-model="prefs.time" @change="changeTime()" :disabled="busy"
                class="rounded-xl border border-tilawa-line bg-tilawa-paper px-3 py-2 text-sm font-semibold text-tilawa-ink focus:border-tilawa-teal-dark focus:outline-none focus:ring-2 focus:ring-tilawa-teal-dark">
        </div>
    @endunless
    <p x-show="problem" x-cloak x-text="problem" role="alert" class="mt-3 rounded-xl bg-[#fff4e0] px-4 py-3 text-xs leading-5 text-[#7A5B14]"></p>
    <p x-show="ready && !supported" x-cloak class="mt-3 text-xs leading-5 text-tilawa-sub">Notifications aren't available in this browser. On iPhone or iPad, add Tilawa to your Home Screen and open it from there.</p>
</section>
