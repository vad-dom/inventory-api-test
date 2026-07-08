<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const string TYPE_INCOME = 'income';

    public const string TYPE_WRITE_OFF = 'write_off';

    public const string TYPE_TRANSFER = 'transfer';

    public const array TYPES = [
        self::TYPE_INCOME,
        self::TYPE_WRITE_OFF,
        self::TYPE_TRANSFER,
    ];

    protected $fillable = [
        'product_id',
        'source_warehouse_id',
        'target_warehouse_id',
        'type',
        'quantity',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }
}
