<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Models;

use App\Casts\Model\MoneyModelCast;
use App\Casts\Model\UuidModelCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperProductLineEloquentModel
 */
final class ProductLineEloquentModel extends Model
{
    protected $table = 'invoice_product_lines';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'id' => UuidModelCast::class,
        'invoice_id' => UuidModelCast::class,
        'price' => MoneyModelCast::class,
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<InvoiceEloquentModel, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceEloquentModel::class, 'invoice_id');
    }
}
