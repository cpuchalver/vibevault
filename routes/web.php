<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

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
