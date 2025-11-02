<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class InvoiceNotFoundException extends DomainException
{
    public static function withId(UuidInterface $id): self
    {
        return new self("Invoice with ID '{$id->toString()}' not found.");
    }
}
