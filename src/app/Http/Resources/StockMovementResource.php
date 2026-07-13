<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'source_warehouse_id' => $this->source_warehouse_id,
            'target_warehouse_id' => $this->target_warehouse_id,
            'quantity' => $this->quantity,
            'type' => $this->type,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
