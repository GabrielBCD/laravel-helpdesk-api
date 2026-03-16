<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketNoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// Public route - create ticket with rate limit
Route::post('/tickets', [TicketController::class, 'store'])
    ->middleware('throttle:5,1');

// Protected routes - ticket management
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/tickets/stats', [TicketController::class, 'stats']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    Route::delete('/tickets/{ticket}/force', [TicketController::class, 'forceDestroy']);

    // Ticket notes
    Route::post('/tickets/{ticket}/notes', [TicketNoteController::class, 'store']);
    Route::put('/tickets/{ticket}/notes/{note}', [TicketNoteController::class, 'update']);
    Route::delete('/tickets/{ticket}/notes/{note}', [TicketNoteController::class, 'destroy']);
    Route::delete('/tickets/{ticket}/notes/{note}/force', [TicketNoteController::class, 'forceDestroy']);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
