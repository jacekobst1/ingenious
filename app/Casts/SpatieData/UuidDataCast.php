<?php

declare(strict_types=1);

namespace App\Casts\SpatieData;

use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class UuidDataCast implements Cast
{
    /**
     * @throws CannotCastEnum
     * @throws Exception
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): UuidInterface
    {
        try {
            return Uuid::fromString($value);
        } catch (Throwable $e) {
            throw new Exception("Cannot cast string $value to UUID");
        }
    }
}
