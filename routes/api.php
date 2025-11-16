<?php

use App\Http\Controllers\MpesaSTKPUSHController;
use Illuminate\Support\Facades\Route;

// Mpesa STK Push Callback Route
Route::post('v1/confirm', [MpesaSTKPUSHController::class, 'STKConfirm'])->name('mpesa.confirm');

