<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Dtos\Requests;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Data;

final class CreateInvoiceRequest extends Data
{
    /**
     * @param list<CreateInvoiceProductLineRequest> $productLines
     */
    public function __construct(
        public string $customerName,
        #[Email]
        public string $customerEmail,
        public array $productLines = [],
    ) {}
}
