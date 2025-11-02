<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Entities;

use Brick\Money\Money;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\MarkInvoiceAsSentException;
use Modules\Invoices\Domain\Exceptions\SendInvoiceException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceTest extends TestCase
{
    public function testInvoiceIsCreatedWithDraftStatus(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $this->assertEquals(StatusEnum::Draft, $invoice->status);
    }

    public function testCanAddProductLine(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $productLine = new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 2,
            unitPrice: Money::of(1000, 'PLN'),
        );

        $invoice->addProductLine($productLine);

        $this->assertCount(1, $invoice->productLines);
        $this->assertSame($productLine, $invoice->productLines[0]);
    }

    public function testCalculatesTotalForMultipleProductLines(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 2,
            unitPrice: Money::of(1000, 'PLN'),
        ));

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product B',
            quantity: 3,
            unitPrice: Money::of(500, 'PLN'),
        ));

        $total = $invoice->calculateTotal();

        $this->assertTrue($total->isEqualTo(Money::of(3500, 'PLN')));
    }

    public function testMarkAsSendingSucceeds(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 1,
            unitPrice: Money::of(1000, 'PLN'),
        ));

        $invoice->markAsSending();

        $this->assertEquals(StatusEnum::Sending, $invoice->status);
    }

    public function testMarkAsSendingFailsWhenNotInDraftStatus(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            status: StatusEnum::SentToClient,
        );

        $this->expectException(SendInvoiceException::class);
        $this->expectExceptionMessage('must be draft');

        $invoice->markAsSending();
    }

    public function testMarkAsSendingFailsWhenNoProductLines(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $this->expectException(SendInvoiceException::class);
        $this->expectExceptionMessage('must have at least one valid product line');

        $invoice->markAsSending();
    }

    public function testMarkAsSentToClientSucceedsWhenInSendingStatus(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 1,
            unitPrice: Money::of(1000, 'PLN'),
        ));

        $invoice->markAsSending();
        $invoice->markAsSentToClient();

        $this->assertEquals(StatusEnum::SentToClient, $invoice->status);
    }

    public function testMarkAsSentToClientFailsWhenNotInSendingStatus(): void
    {
        $invoice = new Invoice(
            id: Uuid::uuid7(),
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $this->expectException(MarkInvoiceAsSentException::class);
        $this->expectExceptionMessage('must be sending');

        $invoice->markAsSentToClient();
    }
}
