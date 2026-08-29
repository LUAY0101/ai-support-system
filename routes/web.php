<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::get('/', [TicketController::class, 'index'])->name('home');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/admin', [TicketController::class, 'admin'])->name('admin');
Route::post('/admin/tickets/{id}/status', [App\Http\Controllers\TicketController::class, 'updateStatus']);
Route::post('/admin/import', [App\Http\Controllers\TicketController::class, 'importExcel']);
Route::post('/admin/ticket/{id}/status', [App\Http\Controllers\TicketController::class, 'updateStatus']);