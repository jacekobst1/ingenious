<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Repositories;

use Illuminate\Database\ConnectionInterface;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Infrastructure\Persistence\Models\InvoiceEloquentModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final readonly class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    public function nextIdentity(): UuidInterface
    {
        return Uuid::uuid7();
    }

    /**
     * @throws Throwable
     */
    public function save(Invoice $invoice): void
    {
        $this->connection->transaction(function () use ($invoice): void {
            $model = InvoiceEloquentModel::create([
                'id' => $invoice->id,
                'customer_name' => $invoice->customerName,
                'customer_email' => $invoice->customerEmail,
                'status' => $invoice->status,
            ]);

            foreach ($invoice->productLines as $line) {
                $model->productLines()->create([
                    'id' => $line->id,
                    'invoice_id' => $invoice->id,
                    'name' => $line->name,
                    'quantity' => $line->quantity,
                    'price' => $line->unitPrice,
                ]);
            }
        });
    }
}
