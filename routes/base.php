<?php

use Illuminate\Support\Facades\Route;
use Convoy\Http\Controllers\Base;

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->where('namespace', '.*');

Route::get('/{any}', [Base\IndexController::class, 'index'])
    ->where('any', '^(?!(\/)?(api|authorize)).+');