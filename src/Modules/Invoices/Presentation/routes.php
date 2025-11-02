<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceController;

Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
