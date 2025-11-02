<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use App\Exceptions\MyDomainException;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

final class EntityNotFoundException extends MyDomainException
{
    public static function invoice(UuidInterface $id): self
    {
        return new self("Invoice with ID '{$id->toString()}' not found.");
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
