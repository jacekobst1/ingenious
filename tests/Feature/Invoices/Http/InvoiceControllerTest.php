<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateInvoiceWithProductLines(): void
    {
        // when
        $response = $this->postJson(route('invoices.store'), [
            'customerName' => 'Jane Smith',
            'customerEmail' => 'jane@example.com',
            'productLines' => [
                [
                    'name' => 'Product A',
                    'quantity' => 2,
                    'unitPrice' => 1000,
                ],
                [
                    'name' => 'Product B',
                    'quantity' => 3,
                    'unitPrice' => 500,
                ],
            ],
        ]);

        // then
        $response->assertCreated();
        $response->assertJsonStructure([
            'message',
            'id',
        ]);

        $this->assertDatabaseHas('invoices', [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('invoice_product_lines', [
            'name' => 'Product A',
            'quantity' => 2,
            'price' => 100000,
        ]);

        $this->assertDatabaseHas('invoice_product_lines', [
            'name' => 'Product B',
            'quantity' => 3,
            'price' => 50000,
        ]);
    }

    public function testValidationFailsForInvalidEmail(): void
    {
        // when
        $response = $this->postJson(route('invoices.store'), [
            'customerName' => 'John Doe',
            'customerEmail' => 'invalid-email',
        ]);

        // then
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['customerEmail']);
    }

    public function testValidationFailsForInvalidProductLineQuantity(): void
    {
        // when
        $response = $this->postJson(route('invoices.store'), [
            'customerName' => 'John Doe',
            'customerEmail' => 'john@example.com',
            'productLines' => [
                [
                    'name' => 'Product A',
                    'quantity' => 0,
                    'unitPrice' => 1000,
                ],
            ],
        ]);

        // then
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['productLines.0.quantity']);
    }

    public function testValidationFailsForInvalidProductLinePrice(): void
    {
        // when
        $response = $this->postJson(route('invoices.store'), [
            'customerName' => 'John Doe',
            'customerEmail' => 'john@example.com',
            'productLines' => [
                [
                    'name' => 'Product A',
                    'quantity' => 1,
                    'unitPrice' => 0,
                ],
            ],
        ]);

        // then
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['productLines.0.unitPrice']);
    }
}
