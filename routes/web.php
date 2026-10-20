<?php

use App\Http\Controllers\AyahOfTheDayController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DailyAyahReminderController;
use App\Http\Controllers\LibraryPdfController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Bookmarks;
use App\Livewire\DailyAyah;
use App\Livewire\Home;
use App\Livewire\IslamicLibrary;
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
Route::get('/daily-ayah', DailyAyah::class)->name('daily-ayah');
Route::get('/daily-ayah/today', AyahOfTheDayController::class)->middleware('throttle:60,1')->name('daily-ayah.today');
Route::get('/library', IslamicLibrary::class)->name('library');
Route::get('/library/hisnul-muslim/read', [LibraryPdfController::class, 'reader'])->name('library.hisnul-muslim.reader');
Route::get('/library/hisnul-muslim/pdf', LibraryPdfController::class)->name('library.hisnul-muslim.pdf');

Route::get('/library/hisnul-muslim/swahili/read', [LibraryPdfController::class, 'reader'])->defaults('edition', 'swahili')->name('library.hisnul-muslim.swahili.reader');
Route::get('/library/hisnul-muslim/swahili/pdf', LibraryPdfController::class)->defaults('edition', 'swahili')->name('library.hisnul-muslim.swahili.pdf');
Route::get('/library/{edition}/read', [LibraryPdfController::class, 'reader'])->where('edition', '[a-z0-9-]+')->name('library.book.reader');
Route::get('/library/{edition}/pdf', LibraryPdfController::class)->where('edition', '[a-z0-9-]+')->name('library.book.pdf');

Route::get('/ruqyah', Ruqyah::class)->name('ruqyah');
Route::view('/mushaf/reader', 'library.mushaf-reader')->name('mushaf.reader');
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
    Route::put('/daily-ayah/reminder', [DailyAyahReminderController::class, 'update'])->middleware('throttle:30,1')->name('daily-ayah.reminder.update');
    Route::delete('/daily-ayah/reminder', [DailyAyahReminderController::class, 'destroy'])->middleware('throttle:30,1')->name('daily-ayah.reminder.destroy');

    Route::post('/logout', function () {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('landing');
    })->name('logout');
});
