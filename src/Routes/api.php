<?php

use Example\CrmExample\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'jwt.tenant'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/contacts', [ContactController::class, 'index']);
        Route::get('/contacts/{id}', [ContactController::class, 'show']);
        Route::post('/contacts', [ContactController::class, 'store']);
        Route::put('/contacts/{id}', [ContactController::class, 'update']);
        Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);
        Route::post('/contacts/{id}/call', [ContactController::class, 'call']);
    }); 