<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException as PhpDomainException;

abstract class MyDomainException extends PhpDomainException
{
    abstract public function getStatusCode(): int;
}
