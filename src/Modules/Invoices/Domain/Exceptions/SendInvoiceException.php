<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use DomainException;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Ramsey\Uuid\UuidInterface;

final class SendInvoiceException extends DomainException
{
    public static function mustBeDraft(UuidInterface $invoiceId, StatusEnum $currentStatus): self
    {
        return new self(
            "Invoice $invoiceId cannot be sent. Current status is $currentStatus->value, but must be draft."
        );
    }

    public static function mustHaveProductLines(UuidInterface $invoiceId): self
    {
        return new self(
            "Invoice $invoiceId cannot be sent. It must have at least one valid product line."
        );
    }
}
