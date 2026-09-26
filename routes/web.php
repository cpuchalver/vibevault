<?php

declare(strict_types=1);

use App\Http\Controllers\SignupsController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Public marketing site
|--------------------------------------------------------------------------
|
| Anonymous, read-only pages. The only write path is the demo request
| Livewire component, which carries its own rate limiting and anti-spam.
|
*/

Route::view('/', 'marketing.home')->name('home');
Route::view('/fonctionnalites', 'marketing.features')->name('features');
Route::view('/tarifs', 'marketing.pricing')->name('pricing');
Route::view('/demo', 'marketing.demo')->name('demo');

Route::name('legal.')->group(function (): void {
    Route::view('/mentions-legales', 'legal.notice')->name('notice');
    Route::view('/confidentialite', 'legal.privacy')->name('privacy');
    Route::view('/cgu', 'legal.terms')->name('terms');
});

/*
|--------------------------------------------------------------------------
| Self-serve signup and subscription
|--------------------------------------------------------------------------
*/

Route::view('/inscription', 'signup.create')
    ->middleware('guest')
    ->name('signup');

Route::middleware('auth')
    ->prefix('/inscription/{label:slug}')
    ->name('signup.')
    ->controller(SignupsController::class)
    ->group(function (): void {
        Route::get('/confirmation', 'completed')->name('completed');
        Route::get('/paiement', 'payment')->name('payment');
        Route::post('/paiement', 'checkout')->middleware('throttle:10,1')->name('checkout');
    });

/*
| Cashier routes, re-registered to enforce webhook signature verification.
*/
Route::prefix(config('cashier.path'))
    ->name('cashier.')
    ->group(function (): void {
        Route::get('payment/{id}', [PaymentController::class, 'show'])->name('payment');
        Route::post('webhook', [StripeWebhookController::class, 'handleWebhook'])->name('webhook');
    });
