<?php

use Illuminate\Support\Facades\Route;
use Modules\Flashcard\Http\Controllers\FlashcardController;

Route::middleware(['auth:sanctum'])
    ->name('flashcards.')
    ->prefix('flashcards')
    ->group(function () {
        Route::get('/', [FlashcardController::class, 'index'])->name('index');
        Route::post('/', [FlashcardController::class, 'store'])->name('store');
        Route::get('/statistics', [FlashcardController::class, 'getStatistics'])->name('statistics');
        Route::post('/reset', [FlashcardController::class, 'resetProgress'])->name('reset');

        Route::get('/{id}', [FlashcardController::class, 'show'])->name('show');
        Route::patch('/{id}', [FlashcardController::class, 'update'])->name('update');
        Route::delete('/{id}', [FlashcardController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [FlashcardController::class, 'restore'])->name('restore');
        Route::get('/{id}/history', [FlashcardController::class, 'getHistory'])->name('history');
        Route::post('/{id}/history', [FlashcardController::class, 'revert'])->name('revert');
    });
