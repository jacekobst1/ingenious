<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use App\Enums\CurrencyEnum;
use Brick\Money\Money;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Services\InvoiceFinder;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Exceptions\EntityNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceFinderTest extends TestCase
{
    private InvoiceRepositoryInterface|MockObject $repository;
    private InvoiceFinder $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->service = new InvoiceFinder($this->repository);
    }

    public function testFindInvoiceWithProductLines(): void
    {
        $invoiceId = Uuid::uuid7();

        // mock
        $invoice = new Invoice(
            id: $invoiceId,
            customerName: 'Jane Smith',
            customerEmail: 'jane@example.com',
        );

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 2,
            unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
        ));

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        // when
        $result = $this->service->find($invoiceId);

        // then
        $this->assertEquals($invoiceId, $result->id);
        $this->assertCount(1, $result->productLines);
        $this->assertEquals('Product A', $result->productLines[0]->name);
        $this->assertEquals(2, $result->productLines[0]->quantity);
        $this->assertTrue($result->productLines[0]->unitPrice->isEqualTo(Money::of(1000, CurrencyEnum::Pln->value)));
    }

    public function testThrowsExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid7();

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Invoice with ID '{$invoiceId->toString()}' not found.");

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn(null);

        $this->service->find($invoiceId);
    }
}
