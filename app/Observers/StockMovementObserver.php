<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $inventory = Inventory::firstOrCreate(
                [
                    'product_id' => $movement->product_id,
                    'warehouse_id' => $movement->warehouse_id,
                ],
                ['quantity' => 0]
            );

            if ($movement->type === 'in') {
                $inventory->increment('quantity', $movement->quantity);
            } elseif ($movement->type === 'out') {
                $inventory->decrement('quantity', $movement->quantity);
            }

            // Note: transfers are handled by recordTransfer creating two movements (one out, one in),
            // so each will be processed here individually.

            $inventory->save();
        });
    }
}
