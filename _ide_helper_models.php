<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace Modules\Invoices\Infrastructure\Persistence\Models{
/**
 * @property \Ramsey\Uuid\UuidInterface $id
 * @property string $customer_name
 * @property string $customer_email
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Invoices\Infrastructure\Persistence\Models\ProductLineEloquentModel> $productLines
 * @property-read int|null $product_lines_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceEloquentModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperInvoiceEloquentModel {}
}

namespace Modules\Invoices\Infrastructure\Persistence\Models{
/**
 * @property \Ramsey\Uuid\UuidInterface $id
 * @property \Ramsey\Uuid\UuidInterface $invoice_id
 * @property string $name
 * @property \Brick\Money\Money $price
 * @property int $quantity
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Modules\Invoices\Infrastructure\Persistence\Models\InvoiceEloquentModel $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductLineEloquentModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperProductLineEloquentModel {}
}

