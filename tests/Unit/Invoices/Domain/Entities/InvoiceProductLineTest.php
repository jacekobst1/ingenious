<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Entities;

use App\Enums\CurrencyEnum;
use Brick\Money\Money;
use InvalidArgumentException;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceProductLineTest extends TestCase
{
    public function testCreatesProductLineSuccessfully(): void
    {
        $id = Uuid::uuid7();
        $unitPrice = Money::of(1000, CurrencyEnum::Pln->value);

        $productLine = new InvoiceProductLine(
            id: $id,
            name: 'Product A',
            quantity: 5,
            unitPrice: $unitPrice,
        );

        $this->assertSame($id, $productLine->id);
        $this->assertEquals('Product A', $productLine->name);
        $this->assertEquals(5, $productLine->quantity);
        $this->assertTrue($productLine->unitPrice->isEqualTo($unitPrice));
    }

    public function testThrowsExceptionWhenQuantityIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a positive integer greater than zero');

        new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 0,
            unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
        );
    }

    public function testThrowsExceptionWhenQuantityIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a positive integer greater than zero');

        new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: -1,
            unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
        );
    }

    public function testThrowsExceptionWhenUnitPriceIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a positive amount greater than zero');

        new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 1,
            unitPrice: Money::zero(CurrencyEnum::Pln->value),
        );
    }

    public function testThrowsExceptionWhenUnitPriceIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a positive amount greater than zero');

        new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 1,
            unitPrice: Money::of(-100, CurrencyEnum::Pln->value),
        );
    }

    public function testCalculateTotalPrice(): void
    {
        $productLine = new InvoiceProductLine(
            id: Uuid::uuid7(),
            name: 'Product A',
            quantity: 5,
            unitPrice: Money::of(1000, CurrencyEnum::Pln->value),
        );

        $total = $productLine->calculateTotalPrice();

        $this->assertTrue($total->isEqualTo(Money::of(5000, CurrencyEnum::Pln->value)));
    }
}
