<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KioskMapController;
use App\Http\Controllers\KioskController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::get('/kiosk/search', [KioskController::class, 'search'])->name('kiosk.search');
