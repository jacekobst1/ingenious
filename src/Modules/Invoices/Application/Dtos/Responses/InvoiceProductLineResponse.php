<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Dtos\Responses;

use Brick\Money\Money;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceProductLineResponse
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
        public int $quantity,
        public Money $unitPrice,
        public Money $totalPrice,
    ) {}

    public static function fromEntity(InvoiceProductLine $line): self
    {
        return new self(
            id: $line->id,
            name: $line->name,
            quantity: $line->quantity,
            unitPrice: $line->unitPrice,
            totalPrice: $line->calculateTotalPrice(),
        );
    }
}
