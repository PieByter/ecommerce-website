<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'sort_order'];

    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Get all descendant category IDs (excluding this category).
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $descendantIds = [];
        $parentIds = [$this->getKey()];

        while ($parentIds !== []) {
            $childIds = static::query()
                ->whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $childIds = array_values(array_diff($childIds, $descendantIds));

            if ($childIds === []) {
                break;
            }

            $descendantIds = array_merge($descendantIds, $childIds);
            $parentIds = $childIds;
        }

        return $descendantIds;
    }

    public function isDescendantOf(self $category): bool
    {
        return $this->getKey() !== $category->getKey()
            && in_array((int) $this->getKey(), $category->descendantIds(), true);
    }

    /**
     * Flatten the category tree into indented select options.
     *
     * @param  array<int, int>  $excludeIds
     * @return array<int, array{id: int, label: string, depth: int}>
     */
    public static function treeOptions(array $excludeIds = []): array
    {
        $categories = static::query()
            ->get(['id', 'parent_id', 'name', 'sort_order'])
            ->sortBy([
                ['sort_order', 'asc'],
                ['name', 'asc'],
            ]);

        $children = $categories->groupBy(fn ($category): int => (int) ($category->parent_id ?? 0));
        $options = [];
        $visited = [];

        $walk = function (int $parentId, int $depth) use (&$walk, &$options, &$visited, $children, $excludeIds): void {
            foreach ($children->get($parentId, collect()) as $category) {
                $categoryId = (int) $category->id;

                if (isset($visited[$categoryId])) {
                    continue;
                }

                $visited[$categoryId] = true;

                if (! in_array($categoryId, $excludeIds, true)) {
                    $options[] = [
                        'id' => $categoryId,
                        'label' => str_repeat('— ', $depth).$category->name,
                        'depth' => $depth,
                    ];
                }

                $walk($categoryId, $depth + 1);
            }
        };

        $walk(0, 0);

        return $options;
    }
}
