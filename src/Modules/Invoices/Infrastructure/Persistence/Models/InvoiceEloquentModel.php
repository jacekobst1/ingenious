<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Models;

use App\Casts\Model\UuidModelCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperInvoiceEloquentModel
 */
final class InvoiceEloquentModel extends Model
{
    public const string RELATION_PRODUCT_LINES = 'productLines';

    protected $table = 'invoices';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'id' => UuidModelCast::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<ProductLineEloquentModel, $this>
     */
    public function productLines(): HasMany
    {
        return $this->hasMany(ProductLineEloquentModel::class, 'invoice_id');
    }
}
