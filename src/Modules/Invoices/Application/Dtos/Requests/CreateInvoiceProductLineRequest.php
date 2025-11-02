<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Dtos\Requests;

use Brick\Money\Money;
use Spatie\LaravelData\Attributes\Validation\GreaterThanOrEqualTo;
use Spatie\LaravelData\Data;

final class CreateInvoiceProductLineRequest extends Data
{
    public function __construct(
        public string $name,
        #[GreaterThanOrEqualTo(1)]
        public int $quantity,
        #[GreaterThanOrEqualTo(1)]
        public Money $unitPrice,
    ) {}
}
