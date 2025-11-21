<?php

use App\Livewire\PosComponent;
use App\Http\Controllers\ThermalPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pos', PosComponent::class)->name('pos');
    
    // Thermal printer routes
    Route::post('/thermal-print/{sale}', [ThermalPrintController::class, 'printReceipt'])->name('thermal.print');
    Route::post('/thermal-print/test', [ThermalPrintController::class, 'testPrinter'])->name('thermal.test');
    Route::get('/thermal-print/diagnostics', [ThermalPrintController::class, 'diagnostics'])->name('thermal.diagnostics');
});
