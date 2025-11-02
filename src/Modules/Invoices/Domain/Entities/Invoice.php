<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\MarkInvoiceAsSentException;
use Modules\Invoices\Domain\Exceptions\SendInvoiceException;
use Ramsey\Uuid\UuidInterface;

/** @param list<InvoiceProductLine> $productLines */
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
    public function calculateTotal(): Money
    {
        $total = Money::zero('PLN');

        foreach ($this->productLines as $line) {
            $total = $total->plus($line->calculateTotal(), RoundingMode::HALF_EVEN);
        }

        return $total;
    }

    public function addProductLine(InvoiceProductLine $line): void
    {
        $this->productLines[] = $line;
    }

    /*
     * -----------------------------------------------------------------------------------------------------------------
     * VALIDATIONS
     */
    public function canBeSent(): bool
    {
        return $this->status === StatusEnum::Draft && $this->hasProductLines();
    }

    public function hasProductLines(): bool
    {
        return !empty($this->productLines);
    }

    /*
     * -----------------------------------------------------------------------------------------------------------------
     * ACTIONS
     */
    public function markAsSending(): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw SendInvoiceException::mustBeDraft($this->id, $this->status);
        }

        if (!$this->hasProductLines()) {
            throw SendInvoiceException::mustHaveProductLines($this->id);
        }

        $this->status = StatusEnum::Sending;
    }

    public function markAsSentToClient(): void
    {
        if ($this->status !== StatusEnum::Sending) {
            throw MarkInvoiceAsSentException::cannotMarkAsSentToClient($this->id, $this->status);
        }

        $this->status = StatusEnum::SentToClient;
    }
}
