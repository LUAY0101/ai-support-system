<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::get('/', [TicketController::class, 'index'])->name('home');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

Route::middleware('auth.basic')->group(function () {
	Route::get('/admin', [TicketController::class, 'admin'])->name('admin');
	Route::post('/admin/tickets/{id}/status', [TicketController::class, 'updateStatus']);
	Route::post('/admin/import', [TicketController::class, 'importExcel']);
});