<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_inventory_valuation");
        DB::statement("
            CREATE VIEW vw_inventory_valuation AS
            SELECT 
                p.id as product_id,
                p.name as product_name,
                p.sku,
                SUM(i.quantity) as total_quantity,
                p.cost as unit_cost,
                SUM(i.quantity * p.cost) as total_value
            FROM products p
            JOIN inventories i ON p.id = i.product_id
            GROUP BY p.id, p.name, p.sku, p.cost
        ");

        DB::statement("DROP VIEW IF EXISTS vw_out_of_stock");
        DB::statement("
            CREATE VIEW vw_out_of_stock AS
            SELECT 
                p.id as product_id,
                p.name as product_name,
                p.sku,
                p.min_stock_level,
                COALESCE(SUM(i.quantity), 0) as current_stock
            FROM products p
            LEFT JOIN inventories i ON p.id = i.product_id
            GROUP BY p.id, p.name, p.sku, p.min_stock_level
            HAVING current_stock <= p.min_stock_level
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_inventory_valuation");
        DB::statement("DROP VIEW IF EXISTS vw_out_of_stock");
    }
};
