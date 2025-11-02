<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Contracts;

use Modules\Invoices\Domain\Entities\Invoice;
use Ramsey\Uuid\UuidInterface;

interface InvoiceRepositoryInterface
{
    public function nextIdentity(): UuidInterface;

    public function findById(UuidInterface $id): ?Invoice;

    public function createWithProductLines(Invoice $invoice): void;

    public function update(Invoice $invoice): void;
}
