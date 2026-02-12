<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'unit_cost',
        'description',
        'user_id',
    ];
}
