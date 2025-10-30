<?php

declare(strict_types=1);

namespace App\Casts\SpatieData;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class DateDataCast implements Cast
{
    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context
    ): CarbonImmutable {
        return CarbonImmutable::parse($value);
    }
}
