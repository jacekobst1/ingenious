<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Invoices\Application\Contracts\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Listeners\MarkInvoiceAsSentToClientListener;
use Modules\Invoices\Infrastructure\Persistence\Repositories\InvoiceRepository;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;

final class InvoiceServiceProvider extends ServiceProvider
{
    public array $singletons = [
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
    ];

    /** @var array<class-string, list<class-string>> */
    protected $listen = [
        ResourceDeliveredEvent::class => [
            MarkInvoiceAsSentToClientListener::class,
        ],
    ];
}
