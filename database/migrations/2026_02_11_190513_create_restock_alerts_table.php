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
        Schema::create('restock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->integer('threshold');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_alerts');
    }
};
