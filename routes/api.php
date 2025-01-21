<?php

use Example\CrmExample\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'jwt.tenant'])
    ->prefix('api/v1')
    ->name('api.contacts.')
    ->group(function () {
        Route::get('contacts', [ContactController::class, 'index'])->name('index');
        Route::post('contacts', [ContactController::class, 'store'])->name('store');
        Route::get('contacts/{id}', [ContactController::class, 'show'])->name('show');
        Route::put('contacts/{id}', [ContactController::class, 'update'])->name('update');
        Route::delete('contacts/{id}', [ContactController::class, 'destroy'])->name('delete');
        Route::post('contacts/{id}/call', [ContactController::class, 'recordCall'])->name('call');
        
        // Additional endpoints for filtering
        Route::get('contacts/by-phone/{phone}', [ContactController::class, 'findByPhone'])->name('by-phone');
        Route::get('contacts/by-email-domain/{domain}', [ContactController::class, 'findByEmailDomain'])->name('by-email');
    }); 