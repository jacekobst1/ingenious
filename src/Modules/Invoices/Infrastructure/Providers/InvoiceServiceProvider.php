<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Persistence\Repositories\InvoiceRepository;

final class InvoiceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public array $singletons = [
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
    ];

    /** @return list<class-string> */
    public function provides(): array
    {
        return [
            InvoiceRepositoryInterface::class,
        ];
    }
}
