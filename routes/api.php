<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/n8n/tickets', [TicketController::class, 'storeFromN8n'])
    ->name('api.n8n.tickets.store');