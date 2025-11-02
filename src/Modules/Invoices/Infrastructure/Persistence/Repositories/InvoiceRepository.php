<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Repositories;

use Illuminate\Database\ConnectionInterface;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
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

    public function findById(UuidInterface $id): ?Invoice
    {
        $model = InvoiceEloquentModel::query()
            ->with(InvoiceEloquentModel::RELATION_PRODUCT_LINES)
            ->find($id->toString());

        if ($model === null) {
            return null;
        }

        $invoice = new Invoice(
            id: $model->id,
            customerName: $model->customer_name,
            customerEmail: $model->customer_email,
            status: StatusEnum::from($model->status),
        );

        foreach ($model->productLines as $lineModel) {
            $invoice->addProductLine(new InvoiceProductLine(
                id: $lineModel->id,
                name: $lineModel->name,
                quantity: $lineModel->quantity,
                unitPrice: $lineModel->price,
            ));
        }

        return $invoice;
    }


    /**
     * @throws Throwable
     */
    public function createWithProductLines(Invoice $invoice): void
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

    public function update(Invoice $invoice): void
    {
        InvoiceEloquentModel::findOrFail($invoice->id)->update([
            'customer_name' => $invoice->customerName,
            'customer_email' => $invoice->customerEmail,
            'status' => $invoice->status,
        ]);
    }
}
