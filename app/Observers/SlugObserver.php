<?php
declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugObserver
{
    /**
     * Handle the model "creating" event.
     * Auto-generate slug from name before saving.
     */
    public function creating(Model $model): void
    {
        if (empty($model->slug) && !empty($model->name)) {
            $model->slug = $this->generateUniqueSlug($model);
        }
    }

    /**
     * Handle the model "updating" event.
     * Regenerate slug when name changes.
     */
    public function updating(Model $model): void
    {
        if ($model->isDirty('name')) {
            $model->slug = $this->generateUniqueSlug($model, $model->id);
        }
    }

    /**
     * Generate a unique slug from the given name.
     */
    private function generateUniqueSlug(Model $model, ?int $excludeId = null): string
    {
        $slug = Str::slug($model->name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($model, $slug, $excludeId)) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists in database.
     */
    private function slugExists(Model $model, string $slug, ?int $excludeId = null): bool
    {
        $query = $model->newQuery()->where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
