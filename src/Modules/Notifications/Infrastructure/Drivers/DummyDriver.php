<?php

declare(strict_types=1);

namespace Modules\Notifications\Infrastructure\Drivers;

use Modules\Notifications\Application\Contracts\DriverInterface;

class DummyDriver implements DriverInterface
{
    public function send(
        string $toEmail,
        string $subject,
        string $message,
        string $reference,
    ): bool {
        return true;
    }
}
