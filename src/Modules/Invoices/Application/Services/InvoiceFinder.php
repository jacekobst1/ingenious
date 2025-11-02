<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Dtos\Responses\InvoiceResponse;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceFinder
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function find(UuidInterface $id): InvoiceResponse
    {
        $invoice = $this->repository->findById($id);

        if ($invoice === null) {
            throw InvoiceNotFoundException::withId($id);
        }

        return InvoiceResponse::fromEntity($invoice);
    }
}
