<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Bookmarks;
use App\Livewire\Home;
use App\Livewire\Juz;
use App\Livewire\Landing;
use App\Livewire\Legal\Privacy;
use App\Livewire\Legal\Terms;
use App\Livewire\Page;
use App\Livewire\Profile;
use App\Livewire\Reading;
use App\Livewire\Ruqyah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Landing::class)->name('landing');

Route::get('/terms', Terms::class)->name('terms');
Route::get('/privacy', Privacy::class)->name('privacy');

Route::get('/home', Home::class)->name('home');
Route::get('/ruqyah', Ruqyah::class)->name('ruqyah');
Route::get('/surah/{number}', Reading::class)->name('surah.show');
Route::get('/page/{number}', Page::class)->name('page.show');
Route::get('/juz/{number}', Juz::class)->name('juz.show');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:10,1')->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:10,1')->name('auth.google.callback');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/bookmarks', Bookmarks::class)->name('bookmarks');
    Route::get('/profile', Profile::class)->name('profile');

    Route::post('/logout', function () {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('landing');
    })->name('logout');
});
