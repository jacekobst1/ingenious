<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use App\Enums\CurrencyEnum;
use Brick\Money\Money;
use Exception;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Dtos\Requests\SendInvoiceRequest;
use Modules\Invoices\Application\Services\InvoiceSender;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\EntityNotFoundException;
use Modules\Invoices\Domain\Exceptions\SendInvoiceException;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class InvoiceSenderTest extends TestCase
{
    private InvoiceRepositoryInterface|MockObject $repository;

    private NotificationFacadeInterface|MockObject $notificationFacade;

    private InvoiceSender $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->notificationFacade = $this->createMock(NotificationFacadeInterface::class);
        $this->service = new InvoiceSender($this->repository, $this->notificationFacade);
    }

    public function testSendInvoiceSuccessfullyWithCustomTitleAndDescription(): void
    {
        $invoiceId = Uuid::uuid7();
        $invoice = $this->createInvoiceWithProductLines($invoiceId);

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify')
            ->with($this->callback(function (NotifyData $data) use ($invoiceId) {
                return $data->resourceId === $invoiceId
                    && $data->toEmail === 'customer@example.com'
                    && $data->subject === 'Custom Title'
                    && $data->message === 'Custom Description';
            }))
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->callback(function (Invoice $inv) use ($invoiceId) {
                return $inv->id === $invoiceId && $inv->status === StatusEnum::Sending;
            }));

        $request = new SendInvoiceRequest(
            title: 'Custom Title',
            description: 'Custom Description',
        );

        // when
        $this->service->send($invoiceId, $request);

        // then - verified through mocks
    }

    public function testSendInvoiceSuccessfullyWithDefaultTitleAndDescription(): void
    {
        $invoiceId = Uuid::uuid7();
        $invoice = $this->createInvoiceWithProductLines($invoiceId);

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify')
            ->with($this->callback(function (NotifyData $data) use ($invoiceId) {
                return $data->resourceId === $invoiceId
                    && $data->toEmail === 'customer@example.com'
                    && $data->subject === 'A new invoice has just been generated for you.'
                    && $data->message === 'Here are the details...';
            }))
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('update');

        $request = new SendInvoiceRequest(
            title: null,
            description: null,
        );

        // when
        $this->service->send($invoiceId, $request);

        // then - verified through mocks
    }

    public function testThrowsExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid7();
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("Invoice with ID '{$invoiceId->toString()}' not found.");

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn(null);

        $this->notificationFacade->expects($this->never())
            ->method('notify');

        $this->repository->expects($this->never())
            ->method('update');

        $request = new SendInvoiceRequest(title: null, description: null);

        // when
        $this->service->send($invoiceId, $request);
    }

    public function testThrowsExceptionWhenInvoiceNotInDraftStatus(): void
    {
        $invoiceId = Uuid::uuid7();
        $this->expectException(SendInvoiceException::class);
        $this->expectExceptionMessage("Invoice $invoiceId cannot be sent. Current status is sending, but must be draft.");

        // given
        $invoice = $this->createInvoiceWithProductLines($invoiceId);
        $invoice->markAsSending();

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify')
            ->willReturn(true);

        $request = new SendInvoiceRequest(title: null, description: null);

        // when
        $this->service->send($invoiceId, $request);
    }

    public function testThrowsExceptionWhenInvoiceHasNoProductLines(): void
    {
        $invoiceId = Uuid::uuid7();
        $this->expectException(SendInvoiceException::class);
        $this->expectExceptionMessage("Invoice $invoiceId cannot be sent. It must have at least one valid product line.");

        $invoice = new Invoice(
            id: $invoiceId,
            customerName: 'Customer Name',
            customerEmail: 'customer@example.com',
            status: StatusEnum::Draft,
            productLines: [],
        );

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify')
            ->willReturn(true);

        $request = new SendInvoiceRequest(title: null, description: null);

        // whe
        $this->service->send($invoiceId, $request);
    }

    public function testThrowsExceptionWhenNotificationFails(): void
    {
        // given
        $invoiceId = Uuid::uuid7();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to send notification');

        $invoice = $this->createInvoiceWithProductLines($invoiceId);

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify')
            ->willReturn(false);

        $this->repository->expects($this->never())
            ->method('update');

        $request = new SendInvoiceRequest(title: null, description: null);

        // when
        $this->service->send($invoiceId, $request);
    }

    public function testRepositoryUpdateIsCalledWithCorrectInvoice(): void
    {
        $invoiceId = Uuid::uuid7();
        $invoice = $this->createInvoiceWithProductLines($invoiceId);

        // mock
        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify')
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->callback(function (Invoice $inv) use ($invoiceId) {
                return $inv->id === $invoiceId
                    && $inv->status === StatusEnum::Sending
                    && $inv->customerName === 'Customer Name'
                    && $inv->customerEmail === 'customer@example.com';
            }));

        $request = new SendInvoiceRequest(title: null, description: null);

        // when
        $this->service->send($invoiceId, $request);

        // then - verified through mocks
    }

    private function createInvoiceWithProductLines(UuidInterface $invoiceId): Invoice
    {
        return new Invoice(
            id: $invoiceId,
            customerName: 'Customer Name',
            customerEmail: 'customer@example.com',
            status: StatusEnum::Draft,
            productLines: [
                new InvoiceProductLine(
                    id: Uuid::uuid7(),
                    name: 'Product A',
                    quantity: 2,
                    unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
                ),
            ],
        );
    }
}
