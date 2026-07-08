<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function sourceStockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'source_warehouse_id');
    }

    public function targetStockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'target_warehouse_id');
    }
}
