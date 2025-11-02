<?php

declare(strict_types=1);

namespace App\Casts\SpatieData;

use App\Enums\CurrencyEnum;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class MoneyDataCast implements Cast
{
    /**
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Money
    {
        $currency = $this->getCurrency();

        return Money::of(
            amount: $value,
            currency: $currency,
            roundingMode: RoundingMode::HALF_UP,
        );
    }

    /**
     * Return fixed currency for demonstration purposes.
     * In real life the currency could be retrieved e.g., from a separate field or user's company settings.
     */
    private function getCurrency(): string
    {
        return CurrencyEnum::Pln->value;
    }
}
