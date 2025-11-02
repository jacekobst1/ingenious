<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Dtos\Responses;

use Brick\Money\Money;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Ramsey\Uuid\UuidInterface;

final readonly class InvoiceResponse
{
    /**
     * @param list<InvoiceProductLineResponse> $productLines
     */
    public function __construct(
        public UuidInterface $id,
        public string $customerName,
        public string $customerEmail,
        public StatusEnum $status,
        public array $productLines,
        public Money $totalPrice,
    ) {}

    public static function fromEntity(Invoice $invoice): self
    {
        $productLines = array_map(
            static fn($line) => InvoiceProductLineResponse::fromEntity($line),
            $invoice->productLines,
        );

        return new self(
            id: $invoice->id,
            customerName: $invoice->customerName,
            customerEmail: $invoice->customerEmail,
            status: $invoice->status,
            productLines: $productLines,
            totalPrice: $invoice->calculateTotal(),
        );
    }
}
