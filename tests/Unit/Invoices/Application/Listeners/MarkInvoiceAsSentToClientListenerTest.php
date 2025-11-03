<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Listeners;

use App\Enums\CurrencyEnum;
use Brick\Money\Money;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Listeners\MarkInvoiceAsSentToClientListener;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class MarkInvoiceAsSentToClientListenerTest extends TestCase
{
    private InvoiceRepositoryInterface|MockObject $repository;

    private LoggerInterface|MockObject $logger;

    private MarkInvoiceAsSentToClientListener $listener;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new MarkInvoiceAsSentToClientListener($this->repository, $this->logger);
    }

    public function testSuccessfullyMarksInvoiceAsSentToClient(): void
    {
        $invoiceId = Uuid::uuid7();

        // given
        $invoice = new Invoice(
            id: $invoiceId,
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
        );

        $invoice->addProductLine(new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 1,
            unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
        ));

        $invoice->markAsSending();

        // mock
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($invoice);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('MarkInvoiceAsSentToClientListener success: Invoice marked as sent to client.', [
                'invoice_id' => $invoiceId->toString(),
            ]);

        // when
        $event = new ResourceDeliveredEvent($invoiceId);
        $this->listener->handle($event);

        // then
        $this->assertEquals(StatusEnum::SentToClient, $invoice->status);
    }

    public function testLogsWarningWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid7();

        // mock
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('MarkInvoiceAsSentToClientListener fail: Invoice not found.', [
                'invoice_id' => $invoiceId->toString(),
            ]);

        // when
        $event = new ResourceDeliveredEvent($invoiceId);
        $this->listener->handle($event);

        // then - verified through mocks
    }

    public function testLogsErrorWhenInvoiceStatusIsNotSending(): void
    {
        $invoiceId = Uuid::uuid7();

        // given
        $invoice = new Invoice(
            id: $invoiceId,
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            status: StatusEnum::Draft,
        );

        // mock
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('MarkInvoiceAsSentToClientListener fail:'),
                [
                    'invoice_id' => $invoiceId->toString(),
                ]
            );

        // when
        $event = new ResourceDeliveredEvent($invoiceId);
        $this->listener->handle($event);

        // then
        $this->assertEquals(StatusEnum::Draft, $invoice->status);
    }

    public function testLogsErrorWhenInvoiceIsAlreadySentToClient(): void
    {
        $invoiceId = Uuid::uuid7();

        // given
        $invoice = new Invoice(
            id: $invoiceId,
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            status: StatusEnum::SentToClient,
        );

        // mock
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->repository
            ->expects($this->never())
            ->method('update');

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('MarkInvoiceAsSentToClientListener fail'),
                [
                    'invoice_id' => $invoiceId->toString(),
                ]
            );

        // when
        $event = new ResourceDeliveredEvent($invoiceId);
        $this->listener->handle($event);

        // then
        $this->assertEquals(StatusEnum::SentToClient, $invoice->status);
    }
}
