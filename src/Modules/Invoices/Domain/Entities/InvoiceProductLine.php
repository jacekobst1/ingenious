<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceProductLine
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
        public int $quantity,
        public Money $unitPrice,
    ) {
        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be a positive integer greater than zero.');
        }

        if ($this->unitPrice->isNegativeOrZero()) {
            throw new InvalidArgumentException('Unit price must be a positive amount greater than zero.');
        }
    }

    public function calculateTotal(): Money
    {
        return $this->unitPrice->multipliedBy($this->quantity, RoundingMode::HALF_EVEN);
    }
}
