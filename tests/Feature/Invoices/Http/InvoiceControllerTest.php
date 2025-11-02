<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Http;

use Brick\Money\Money;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Infrastructure\Persistence\Models\InvoiceEloquentModel;
use Modules\Invoices\Infrastructure\Persistence\Models\ProductLineEloquentModel;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class InvoiceControllerTest extends TestCase
{
    public function testShowInvoice(): void
    {
        // given
        $invoiceModel = InvoiceEloquentModel::create([
            'id' => Uuid::uuid7(),
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => 'draft',
        ]);
        ProductLineEloquentModel::create([
            'id' => Uuid::uuid7(),
            'invoice_id' => $invoiceModel->id,
            'name' => 'Product A',
            'quantity' => 2,
            'price' => Money::of(1000, 'PLN'),
        ]);

        // when
        $response = $this->getJson(route('invoices.show', ['id' => $invoiceModel->id]));

        // then
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'customerName',
            'customerEmail',
            'status',
            'productLines' => [[
                'id',
                'name',
                'quantity',
                'unitPrice',
                'totalPrice',
            ]],
            'totalPrice',
        ]);

        $json = static fn(string $key) => $response->json($key);

        $this->assertEquals($invoiceModel->id, $json('id'));
        $this->assertEquals('Test User', $json('customerName'));
        $this->assertEquals('test@example.com', $json('customerEmail'));
        $this->assertEquals('draft', $json('status'));
        $this->assertCount(1, $json('productLines'));
        $this->assertEquals('Product A', $json('productLines.0.name'));
    }

    public function testShowInvoiceReturnsNotFoundWhenInvoiceDoesNotExist(): void
    {
        // when
        $response = $this->getJson(route('invoices.show', ['id' => '550e8400-e29b-41d4-a716-446655440000']));

        // then
        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }

    public function testShowInvoiceReturnsNotFoundForInvalidUuid(): void
    {
        // when
        $response = $this->getJson(route('invoices.show', ['id' => 'invalid-uuid-format']));

        // then
        $response->assertBadRequest();
        $response->assertJsonStructure(['message']);
        $this->assertEquals('Invalid UUID string: invalid-uuid-format', $response->json('message'));
    }

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

    public function testSendInvoiceSuccessfully(): void
    {
        // given
        $invoiceModel = InvoiceEloquentModel::create([
            'id' => Uuid::uuid7(),
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => 'draft',
        ]);
        ProductLineEloquentModel::create([
            'id' => Uuid::uuid7(),
            'invoice_id' => $invoiceModel->id,
            'name' => 'Product A',
            'quantity' => 2,
            'price' => Money::of(1000, 'PLN'),
        ]);

        // mock
        $this->mock(NotificationFacadeInterface::class, static function ($mock): void {
            $mock->shouldReceive('notify')->once()->andReturn(true);
        });

        // when
        $response = $this->postJson(route('invoices.send', ['id' => $invoiceModel->id]), [
            'title' => 'Your Invoice',
            'description' => 'Please review the invoice',
        ]);

        // then
        $response->assertOk();
        $response->assertJson(['message' => 'success']);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoiceModel->id,
            'status' => StatusEnum::Sending->value,
        ]);
    }
}
