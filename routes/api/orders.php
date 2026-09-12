<?php

use App\Modules\Order\Presentation\Http\Controllers\OrderController;
use App\Modules\Order\Presentation\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::patch('returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
    Route::patch('returns/{return}/reject', [ReturnController::class, 'reject'])->name('returns.reject');
});
