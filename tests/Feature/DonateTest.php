<?php

use App\Livewire\Donate;
use App\Models\Donation;
use App\Services\ClickPesa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    config(['services.clickpesa.client_id' => 'cid', 'services.clickpesa.api_key' => 'key', 'services.clickpesa.checksum_key' => null]);
});

test('the donate page renders both steps and the footer links to it', function () {
    $this->get(route('donate'))->assertOk()->assertSee('Amount To Pay')->assertSee('Select Payment Method')->assertSee('Make Payment');
    $this->get(route('landing'))->assertOk()->assertSee(route('donate'), false);
});

test('a valid donation creates a record and redirects to the ClickPesa checkout', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/third-parties/generate-token' => Http::response(['success' => true, 'token' => 'Bearer abc']),
        '*/third-parties/checkout-link/generate-checkout-url' => Http::response(['checkoutLink' => 'https://checkout.clickpesa.com/pay/xyz']),
    ]);

    Livewire::test(Donate::class)->call('pay', '10,000', 'mobile')->assertRedirect('https://checkout.clickpesa.com/pay/xyz');

    $donation = Donation::first();
    expect($donation->amount)->toBe(10000)->and($donation->currency)->toBe('TZS')->and($donation->status)->toBe('pending')
        ->and($donation->reference)->toMatch('/^TLW[A-Z0-9]+$/');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'generate-checkout-url')
        && $r->hasHeader('Authorization', 'Bearer abc')
        && $r['totalPrice'] === '10000' && $r['orderCurrency'] === 'TZS' && $r['orderReference'] === $donation->reference);
});

test('amounts below the minimum are rejected without calling ClickPesa', function () {
    Http::preventStrayRequests();

    Livewire::test(Donate::class)->call('pay', '50', 'mobile')->assertHasErrors('amount');
    expect(Donation::count())->toBe(0);
});

test('a ClickPesa failure shows an error and marks the donation failed', function () {
    Http::fake(['*' => Http::response(['message' => 'boom'], 500)]);

    Livewire::test(Donate::class)->call('pay', '5000', 'card')->assertHasErrors('amount')->assertNoRedirect();
    expect(Donation::first()->status)->toBe('failed');
});

test('the callback marks a donation paid and is idempotent', function () {
    $d = Donation::create(['reference' => 'TLWABC123', 'amount' => 5000]);

    $this->postJson(route('donations.callback'), ['status' => 'SUCCESS', 'orderReference' => 'TLWABC123', 'paymentReference' => 'P1'])->assertOk();
    expect($d->fresh()->status)->toBe('paid')->and($d->fresh()->paid_at)->not->toBeNull();

    $this->postJson(route('donations.callback'), ['status' => 'FAILED', 'orderReference' => 'TLWABC123'])->assertOk();
    expect($d->fresh()->status)->toBe('paid');
});

test('the callback rejects a bad checksum when a checksum key is configured', function () {
    config(['services.clickpesa.checksum_key' => 'secret']);
    Donation::create(['reference' => 'TLWABC123', 'amount' => 5000]);

    $payload = ['status' => 'SUCCESS', 'orderReference' => 'TLWABC123'];
    $this->postJson(route('donations.callback'), $payload + ['checksum' => 'nope'])->assertForbidden();
    $this->postJson(route('donations.callback'), $payload + ['checksum' => ClickPesa::checksum('secret', $payload)])->assertOk();
    expect(Donation::first()->status)->toBe('paid');
});
