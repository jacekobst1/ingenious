<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use DomainException;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Ramsey\Uuid\UuidInterface;

final class MarkInvoiceAsSenException extends DomainException
{
    public static function cannotMarkAsSentToClient(
        UuidInterface $invoiceId,
        StatusEnum $currentStatus
    ): self {
        return new self(
            "Invoice $invoiceId cannot be marked as sent to client. Current status is $currentStatus->value, but must be sending."
        );
    }
}
