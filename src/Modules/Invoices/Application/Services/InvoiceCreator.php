<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\Dtos\Requests\CreateInvoiceRequest;
use Modules\Invoices\Application\Dtos\Responses\InvoiceResponse;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;

final readonly class InvoiceCreator
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function create(CreateInvoiceRequest $data): InvoiceResponse
    {
        $invoice = new Invoice(
            id: $this->repository->nextIdentity(),
            customerName: $data->customerName,
            customerEmail: $data->customerEmail,
        );

        foreach ($data->productLines as $lineData) {
            $invoice->addProductLine(new InvoiceProductLine(
                id: $this->repository->nextIdentity(),
                name: $lineData->name,
                quantity: $lineData->quantity,
                unitPrice: $lineData->unitPrice,
            ));
        }

        $this->repository->save($invoice);

        return InvoiceResponse::fromEntity($invoice);
    }
}
