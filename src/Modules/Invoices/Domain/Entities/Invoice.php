<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\MarkInvoiceAsSentException;
use Modules\Invoices\Domain\Exceptions\SendInvoiceException;
use Ramsey\Uuid\UuidInterface;

/** @property list<InvoiceProductLine> $productLines */
final class Invoice
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly string $customerName,
        public readonly string $customerEmail,
        private(set) StatusEnum $status = StatusEnum::Draft,
        private(set) array $productLines = [],
    ) {}

    /*
     * -----------------------------------------------------------------------------------------------------------------
     * GETTERS & SETTERS
     */
    public function calculateTotalPrice(): Money
    {
        $total = Money::zero('PLN');

        foreach ($this->productLines as $line) {
            $total = $total->plus($line->calculateTotalPrice(), RoundingMode::HALF_EVEN);
        }

        return $total;
    }

    public function addProductLine(InvoiceProductLine $line): void
    {
        $this->productLines[] = $line;
    }

    /*
     * -----------------------------------------------------------------------------------------------------------------
     * ACTIONS
     */
    public function markAsSending(): true
    {
        if ($this->status !== StatusEnum::Draft) {
            throw SendInvoiceException::mustBeDraft($this->id, $this->status);
        }

        if (empty($this->productLines)) {
            throw SendInvoiceException::mustHaveProductLines($this->id);
        }

        $this->status = StatusEnum::Sending;

        return true;
    }

    public function markAsSentToClient(): true
    {
        if ($this->status !== StatusEnum::Sending) {
            throw MarkInvoiceAsSentException::cannotMarkAsSentToClient($this->id, $this->status);
        }

        $this->status = StatusEnum::SentToClient;

        return true;
    }
}
