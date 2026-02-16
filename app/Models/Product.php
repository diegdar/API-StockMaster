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
        'unit_cost',
        'valuation_strategy',
        'min_stock_level',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function restockAlerts()
    {
        return $this->hasMany(RestockAlert::class);
    }
}
