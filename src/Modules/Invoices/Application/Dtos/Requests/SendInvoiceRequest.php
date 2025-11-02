<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Dtos\Requests;

use Spatie\LaravelData\Data;

final class SendInvoiceRequest extends Data
{
    public function __construct(
        public ?string $title,
        public ?string $description,
    ) {}
}
