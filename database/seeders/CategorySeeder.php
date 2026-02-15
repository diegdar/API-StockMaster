<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Database\Seeders\Traits\DisablesForeignKeyChecking;

class CategorySeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        Category::truncate();

        $this->enableForeignKeyChecking();

        // Hardcoded base categories for consistent data
        Category::create(['name' => 'Ferretería General', 'slug' => 'ferreteria-general', 'description' => 'Artículos de ferretería general']);
        Category::create(['name' => 'Electricidad', 'slug' => 'electricidad', 'description' => 'Materiales y componentes eléctricos']);
        Category::create(['name' => 'Fontanería', 'slug' => 'fontaneria', 'description' => 'Artículos de fontanería y plumbing']);

        // Additional random categories using factory
        Category::factory()->count(2)->create();
    }
}
