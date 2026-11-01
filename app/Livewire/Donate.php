<?php

namespace App\Livewire;

use App\Models\Donation;
use App\Services\ClickPesa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app', ['title' => 'Support Tilawa'])]
class Donate extends Component
{
    public function pay(string $amount, string $method, string $currency, ClickPesa $clickPesa)
    {
        $currency = strtoupper($currency);
        $min = (int) config($currency === 'USD' ? 'services.clickpesa.min_amount_usd' : 'services.clickpesa.min_amount', 500);

        $data = Validator::make(
            ['amount' => preg_replace('/\D/', '', $amount), 'method' => $method, 'currency' => $currency],
            ['amount' => ['required', 'integer', 'min:'.$min, 'max:5000000'], 'method' => ['required', 'in:mobile'], 'currency' => ['required', 'in:TZS,USD']],
            ['amount.min' => 'The minimum donation is '.$currency.' '.number_format($min).'.', 'method.in' => 'Card payments are coming soon. Please use Mobile Network.', 'amount.required' => 'Enter an amount.', 'amount.integer' => 'Enter a valid amount.', 'amount.max' => 'That amount is too large.'],
        )->validate();

        $donation = Donation::create([
            'reference' => 'TLW'.strtoupper(Str::random(14)),
            'user_id' => auth()->id(),
            'amount' => (int) $data['amount'],
            'currency' => $data['currency'],
            'method' => $data['method'],
        ]);

        try {
            $link = $clickPesa->checkoutLink($donation);
        } catch (Throwable $e) {
            report($e);
            $donation->update(['status' => 'failed']);
            $this->addError('amount', 'Payments are unavailable right now. Please try again shortly.'.($e instanceof \RuntimeException && str_starts_with($e->getMessage(), 'ClickPesa') ? ' ['.$e->getMessage().']' : ''));

            return;
        }

        $donation->update(['checkout_link' => $link]);

        return redirect()->away($link);
    }

    public function render()
    {
        return view('livewire.donate', [
            'min' => (int) config('services.clickpesa.min_amount', 500),
            'minUsd' => (int) config('services.clickpesa.min_amount_usd', 500),
        ]);
    }
}
