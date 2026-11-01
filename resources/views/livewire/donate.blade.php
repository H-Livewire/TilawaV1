<div class="min-h-screen bg-tilawa-surface text-tilawa-ink"
    x-data="{
        amount: '10,000', method: 'mobile', open1: true, open2: true, busy: false,
        fmt() { const v = this.amount.replace(/\D/g, ''); this.amount = v ? Number(v).toLocaleString('en-US') : ''; },
    }"
    x-on:livewire-upload-error.window="busy = false">
    <header class="rounded-b-[2.5rem] bg-tilawa-teal px-6 pb-8 pt-6 text-white md:px-12">
        <div class="mx-auto max-w-xl">
            <a href="{{ route('landing') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20"><x-ui.icon name="arrow-left" class="h-5 w-5" /></span>
                Back
            </a>
            <h1 class="mt-4 text-2xl font-extrabold">Support Tilawa</h1>
            <p class="mt-1 text-sm text-white/90">Your gift helps keep Tilawa free for everyone to read.</p>
        </div>
    </header>

    <main class="mx-auto max-w-xl px-3 pt-4">
        <div class="overflow-hidden rounded-md border border-tilawa-line">
            {{-- 1. Amount --}}
            <button type="button" @click="open1 = !open1" :aria-expanded="open1"
                class="flex w-full items-center gap-3 border-b border-tilawa-line bg-tilawa-paper px-4 py-4 text-left text-lg font-extrabold">
                <svg class="h-4 w-4 shrink-0 transition" :class="open1 ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-tilawa-teal text-sm font-extrabold text-white">1</span>
                Amount To Pay
            </button>
            <div x-show="open1" class="px-4 pb-8 pt-5">
                <label for="amount" class="mb-2 block text-xl text-tilawa-sub">Amount</label>
                <div class="flex items-center rounded-lg border border-tilawa-line py-2 pl-4 pr-2">
                    <input id="amount" type="text" inputmode="numeric" autocomplete="off" x-model="amount" @input="fmt()"
                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-lg font-bold text-tilawa-ink outline-none focus:ring-0" />
                    <span class="flex items-center gap-2 rounded-lg bg-tilawa-paper px-3 py-3 text-sm font-bold">
                        <span class="inline-block h-5 w-5 rounded-full" style="background:linear-gradient(135deg,#1eb53a 0 40%,#000 40% 60%,#00a3dd 60%)"></span>TZS
                    </span>
                </div>
                @error('amount') <p class="mt-2 text-sm font-semibold text-red-600" role="alert">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-tilawa-sub">Minimum TZS {{ number_format($min) }}.</p>
            </div>

            {{-- 2. Payment method --}}
            <button type="button" @click="open2 = !open2" :aria-expanded="open2"
                class="flex w-full items-center gap-3 border-y border-tilawa-line bg-tilawa-paper px-4 py-4 text-left text-lg font-extrabold">
                <svg class="h-4 w-4 shrink-0 transition" :class="open2 ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-tilawa-teal text-sm font-extrabold text-white">2</span>
                Select Payment Method
            </button>
            <div x-show="open2" class="space-y-3.5 px-4 pb-6 pt-5">
                <button type="button" @click="method = 'mobile'" :aria-pressed="method === 'mobile'"
                    class="flex w-full items-center gap-4 rounded-md border bg-tilawa-surface px-4 py-5 text-left"
                    :class="method === 'mobile' ? 'border-tilawa-teal ring-1 ring-tilawa-teal' : 'border-tilawa-line'">
                    <span class="flex w-[88px] shrink-0 items-center justify-center gap-1" aria-hidden="true">
                        <i class="h-3 w-3 rounded-full bg-[#e40000]"></i><i class="h-3 w-3 rounded-full bg-[#1b4f9c]"></i><i class="h-3 w-3 rounded-full bg-[#f7b500]"></i><i class="h-3 w-3 rounded-full bg-[#00a859]"></i>
                    </span>
                    <span class="flex-1">
                        <span class="block text-[17px] font-bold">Mobile Network</span>
                        <span class="mt-2 block text-sm text-tilawa-sub">Pay with Airtel Money, Tigo Pesa, M-Pesa or HaloPesa</span>
                    </span>
                    <span class="text-xs text-tilawa-sub"><span class="mb-1.5 block h-6 w-6 rounded-full" style="background:linear-gradient(135deg,#1eb53a 0 40%,#000 40% 60%,#00a3dd 60%)"></span>TZS</span>
                </button>
                <button type="button" @click="method = 'card'" :aria-pressed="method === 'card'"
                    class="flex w-full items-center gap-4 rounded-md border bg-tilawa-surface px-4 py-5 text-left"
                    :class="method === 'card' ? 'border-tilawa-teal ring-1 ring-tilawa-teal' : 'border-tilawa-line'">
                    <span class="flex w-[88px] shrink-0 items-center justify-center"><img src="{{ asset('images/payments/visa-mastercard.webp') }}" alt="Visa and Mastercard" class="h-auto w-[88px]" loading="lazy" /></span>
                    <span class="flex-1">
                        <span class="block text-[17px] font-bold">Pay with Visa Cards</span>
                        <span class="mt-2 block text-sm text-tilawa-sub">Pay with VISA, MasterCard, or UnionPay</span>
                    </span>
                    <span class="text-xs text-tilawa-sub"><span class="mb-1.5 block h-6 w-6 rounded-full" style="background:linear-gradient(135deg,#b22234 0 50%,#3c3b6e 50%)"></span>TZS</span>
                </button>
            </div>
        </div>

        <div class="sticky bottom-0 mt-4 flex justify-end border-t border-tilawa-line bg-tilawa-surface px-1 py-3">
            <button type="button" @click="busy = true; $wire.pay(amount, method).then(() => busy = false)" :disabled="busy"
                class="w-[62%] rounded-lg bg-tilawa-teal py-4 text-[17px] font-bold text-white shadow-md shadow-tilawa-teal/30 transition hover:brightness-105 disabled:opacity-70">
                <span x-show="!busy">Make Payment</span><span x-show="busy" x-cloak>Please wait…</span>
            </button>
        </div>
        <p class="pb-8 text-center text-xs text-tilawa-sub">You will be taken to ClickPesa's secure page to complete payment.</p>
    </main>
</div>
