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
    public function pay(string $amount, string $method, ClickPesa $clickPesa)
    {
        $min = (int) config('services.clickpesa.min_amount', 100);

        $data = Validator::make(
            ['amount' => preg_replace('/\D/', '', $amount), 'method' => $method],
            ['amount' => ['required', 'integer', 'min:'.$min, 'max:5000000'], 'method' => ['required', 'in:mobile,card']],
            ['amount.min' => 'The minimum donation is TZS '.number_format($min).'.', 'amount.required' => 'Enter an amount.', 'amount.integer' => 'Enter a valid amount.', 'amount.max' => 'That amount is too large.'],
        )->validate();

        $donation = Donation::create([
            'reference' => 'TLW'.strtoupper(Str::random(14)),
            'user_id' => auth()->id(),
            'amount' => (int) $data['amount'],
            'currency' => 'TZS',
            'method' => $data['method'],
        ]);

        try {
            $link = $clickPesa->checkoutLink($donation);
        } catch (Throwable $e) {
            report($e);
            $donation->update(['status' => 'failed']);
            $this->addError('amount', 'Payments are unavailable right now. Please try again shortly.');

            return;
        }

        $donation->update(['checkout_link' => $link]);

        return redirect()->away($link);
    }

    public function render()
    {
        return view('livewire.donate', ['min' => (int) config('services.clickpesa.min_amount', 100)]);
    }
}
