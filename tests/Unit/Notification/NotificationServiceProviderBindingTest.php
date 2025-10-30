<?php

declare(strict_types=1);

namespace Tests\Unit\Notification;

use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Tests\TestCase;

final class NotificationServiceProviderBindingTest extends TestCase
{
    /**
     * @throws BindingResolutionException
     */
    public function test_notification_facade_interface_is_bound_as_singleton(): void
    {
        // Resolve the interface multiple times
        $instance1 = $this->app->make(NotificationFacadeInterface::class);
        $instance2 = $this->app->make(NotificationFacadeInterface::class);
        $instance3 = $this->app->make(NotificationFacadeInterface::class);

        // Same instance within the request lifecycle
        $this->assertSame($instance1, $instance2);
        $this->assertSame($instance2, $instance3);

        // Simulate new request lifecycle
        $this->app->forgetScopedInstances();

        $instance4 = $this->app->make(NotificationFacadeInterface::class);

        // Singleton: same instance even after forgetScopedInstances()
        // This is CORRECT behavior for stateless services
        $this->assertSame($instance1, $instance4);
    }
}
