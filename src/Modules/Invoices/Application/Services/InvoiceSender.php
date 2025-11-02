<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Exception;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Dtos\Requests\SendInvoiceRequest;
use Modules\Invoices\Domain\Exceptions\EntityNotFoundException;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceSender
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private NotificationFacadeInterface $notificationFacade,
    ) {}

    /**
     * @throws Exception
     */
    public function send(UuidInterface $id, SendInvoiceRequest $data): void
    {
        $invoice = $this->repository->findById($id);

        if ($invoice === null) {
            throw EntityNotFoundException::invoice($id);
        }

        $notificationSent = $this->notificationFacade->notify(new NotifyData(
            resourceId: $invoice->id,
            toEmail: $invoice->customerEmail,
            subject: $data->title ?? 'A new invoice has just been generated for you.',
            message: $data->description ?? 'Here are the details...',
        ));

        // I suppose that the notification module is capable of retrying the notification sent if any error occurs.
        // If it fails anyway, we should throw the generic exception. Not the domain one, because it's not a domain issue, but rather the infrastructure one.
        if (!$notificationSent) {
            throw new Exception('Failed to send notification');
        }

        $invoice->markAsSending();
        $this->repository->update($invoice);
    }
}
