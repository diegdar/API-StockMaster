<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'supplier_id',
        'sku',
        'unit_price',
        'valuation_strategy',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
