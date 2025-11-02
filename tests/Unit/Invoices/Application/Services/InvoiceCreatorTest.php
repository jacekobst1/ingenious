<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use App\Enums\CurrencyEnum;
use Brick\Money\Money;
use Modules\Invoices\Application\Dtos\Requests\CreateInvoiceRequest;
use Modules\Invoices\Application\Dtos\Requests\CreateInvoiceProductLineRequest;
use Modules\Invoices\Application\Services\InvoiceCreator;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Enums\StatusEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceCreatorTest extends TestCase
{
    private InvoiceRepositoryInterface|MockObject $repository;

    private InvoiceCreator $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->service = new InvoiceCreator($this->repository);
    }

    public function testCreateInvoiceWithoutProductLines(): void
    {
        // mock
        $uuid = Uuid::uuid7();

        $this->repository->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($uuid);

        $this->repository->expects($this->once())
            ->method('createWithProductLines')
            ->with($this->callback(function (Invoice $invoice) use ($uuid) {
                return $invoice->id === $uuid
                    && $invoice->customerName === 'John Doe'
                    && $invoice->customerEmail === 'john@example.com'
                    && $invoice->status === StatusEnum::Draft
                    && count($invoice->productLines) === 0;
            }));

        // given
        $data = new CreateInvoiceRequest(
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            productLines: [],
        );

        // when
        $result = $this->service->create($data);

        // then
        $this->assertEquals($uuid, $result->id);
        $this->assertEquals('John Doe', $result->customerName);
        $this->assertEquals('john@example.com', $result->customerEmail);
        $this->assertEquals(StatusEnum::Draft, $result->status);
        $this->assertCount(0, $result->productLines);
    }

    public function testCreateInvoiceWithProductLines(): void
    {
        // mock
        $invoiceId = Uuid::uuid7();
        $lineId = Uuid::uuid7();

        $this->repository->expects($this->exactly(2))
            ->method('nextIdentity')
            ->willReturn($invoiceId, $lineId);

        $this->repository->expects($this->once())
            ->method('createWithProductLines')
            ->with($this->callback(function (Invoice $invoice) use ($invoiceId) {
                return $invoice->id === $invoiceId
                    && $invoice->customerName === 'Jane Smith'
                    && $invoice->customerEmail === 'jane@example.com'
                    && $invoice->status === StatusEnum::Draft
                    && count($invoice->productLines) === 1
                    && $invoice->productLines[0]->name === 'Product A'
                    && $invoice->productLines[0]->quantity === 2
                    && $invoice->productLines[0]->unitPrice->isEqualTo(Money::of(1000, CurrencyEnum::Pln->value));
            }));

        // given
        $data = new CreateInvoiceRequest(
            customerName: 'Jane Smith',
            customerEmail: 'jane@example.com',
            productLines: [
                new CreateInvoiceProductLineRequest(
                    name: 'Product A',
                    quantity: 2,
                    unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
                ),
            ],
        );

        // when
        $result = $this->service->create($data);

        // then
        $this->assertEquals($invoiceId, $result->id);
        $this->assertEquals('Jane Smith', $result->customerName);
        $this->assertEquals('jane@example.com', $result->customerEmail);
        $this->assertEquals(StatusEnum::Draft, $result->status);
        $this->assertCount(1, $result->productLines);
        $this->assertEquals('Product A', $result->productLines[0]->name);
        $this->assertEquals(2, $result->productLines[0]->quantity);
        $this->assertTrue($result->productLines[0]->unitPrice->isEqualTo(1000));
    }

    public function testCalculatesTotalForProductLines(): void
    {
        // mock
        $invoiceId = Uuid::uuid7();
        $lineId1 = Uuid::uuid7();
        $lineId2 = Uuid::uuid7();

        $this->repository->expects($this->exactly(3))
            ->method('nextIdentity')
            ->willReturn($invoiceId, $lineId1, $lineId2);

        $this->repository->expects($this->once())
            ->method('createWithProductLines');

        // given
        $data = new CreateInvoiceRequest(
            customerName: 'Test User',
            customerEmail: 'test@example.com',
            productLines: [
                new CreateInvoiceProductLineRequest(name: 'Product A', quantity: 2, unitPrice: Money::of(1000, CurrencyEnum::Pln->value)),
                new CreateInvoiceProductLineRequest(name: 'Product B', quantity: 3, unitPrice: Money::of(500, CurrencyEnum::Pln->value)),
            ],
        );

        // when
        $result = $this->service->create($data);

        // then
        $this->assertTrue($result->totalPrice->isEqualTo(3500));
    }
}
