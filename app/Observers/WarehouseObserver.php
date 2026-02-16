<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Warehouse;
use Illuminate\Support\Str;

class WarehouseObserver
{
    /**
     * Handle the Warehouse "creating" event.
     * Auto-generate slug from name before saving.
     */
    public function creating(Warehouse $warehouse): void
    {
        if (empty($warehouse->slug)) {
            $warehouse->slug = $this->generateUniqueSlug($warehouse->name);
        }
    }

    /**
     * Handle the Warehouse "updating" event.
     * Regenerate slug when name changes.
     */
    public function updating(Warehouse $warehouse): void
    {
        if ($warehouse->isDirty('name')) {
            $warehouse->slug = $this->generateUniqueSlug($warehouse->name, $warehouse->id);
        }
    }

    /**
     * Generate a unique slug from the given name.
     */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists in database.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Warehouse::where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
