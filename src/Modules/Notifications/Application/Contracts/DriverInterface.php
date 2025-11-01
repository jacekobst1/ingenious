<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Contracts;

interface DriverInterface
{
    public function send(
        string $toEmail,
        string $subject,
        string $message,
        string $reference,
    ): bool;
}
