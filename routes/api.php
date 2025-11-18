<?php

use App\Http\Controllers\BankPaybillSTKPushController;
use App\Http\Controllers\MpesaSTKPUSHController;
use Illuminate\Support\Facades\Route;

// Mpesa STK Push Callback Route
Route::post('v1/confirm', [MpesaSTKPUSHController::class, 'STKConfirm'])->name('mpesa.confirm');

// Bank Paybill STK Push Routes
Route::post('bank-paybill/stk-push', [BankPaybillSTKPushController::class, 'STKPush'])->name('bank-paybill.stk-push');
Route::post('bank-paybill/stk-confirm', [BankPaybillSTKPushController::class, 'STKConfirm'])->name('bank-paybill.stk-confirm');
Route::get('bank-paybill/status/{checkoutId}', [BankPaybillSTKPushController::class, 'checkStatus'])->name('bank-paybill.status');

