<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use App\Exceptions\MyDomainException;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

final class MarkInvoiceAsSentException extends MyDomainException
{
    public static function cannotMarkAsSentToClient(
        UuidInterface $invoiceId,
        StatusEnum $currentStatus
    ): self {
        return new self(
            "Invoice $invoiceId cannot be marked as sent to client. Current status is $currentStatus->value, but must be sending."
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
