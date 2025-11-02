<?php

declare(strict_types=1);

namespace App\Casts\Model;

use App\Enums\CurrencyEnum;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class MoneyModelCast implements CastsAttributes
{
    /**
     * Cast the given value, after retrieving from db.
     *
     * @param array<string, mixed> $attributes
     *
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        $currency = $this->getCurrency();

        return Money::ofMinor($value, $currency);
    }

    /**
     * Prepare the given value for putting into db.
     *
     * @param array<string, mixed> $attributes
     *
     * @throws InvalidArgumentException
     * @throws MathException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new InvalidArgumentException('Value should be an instance of Money class');
        }

        return $value->getMinorAmount()->toInt();
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
