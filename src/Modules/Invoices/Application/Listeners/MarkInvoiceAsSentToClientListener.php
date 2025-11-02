<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MarkInvoiceAsSentToClientListener implements ShouldQueue
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private LoggerInterface $logger,
    ) {}

    public function handle(ResourceDeliveredEvent $event): void
    {
        $invoice = $this->repository->findById($event->resourceId);

        if ($invoice === null) {
            $this->logger->warning(
                'MarkInvoiceAsSentToClientListener fail: Invoice not found.',
                ['invoice_id' => $event->resourceId],
            );
            return;
        }

        try {
            $invoice->markAsSentToClient();
            $this->repository->update($invoice);
        } catch (Throwable $e) {
            $this->logger->error(
                'MarkInvoiceAsSentToClientListener fail: ' . $e->getMessage(),
                ['invoice_id' => $event->resourceId],
            );
            return;
        }

        $this->logger->info(
            'MarkInvoiceAsSentToClientListener success: Invoice marked as sent to client.',
            ['invoice_id' => $event->resourceId],
        );
    }
}
