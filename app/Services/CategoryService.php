<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CategoryService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Category
    {
        $slug = $this->uniqueSlug(
            (string) ($data['slug'] ?? '') !== '' ? (string) $data['slug'] : (string) $data['name'],
        );

        return Category::query()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $slug,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function update(Category $category, array $data): void
    {
        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;

        if ($parentId === (int) $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Kategori tidak boleh menjadi induk dari dirinya sendiri.',
            ]);
        }

        if ($parentId !== null) {
            $parent = Category::query()->find($parentId);

            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Kategori induk yang dipilih tidak valid.',
                ]);
            }

            if ($parent->isDescendantOf($category)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Kategori induk tidak boleh berasal dari subkategori kategori ini.',
                ]);
            }
        }

        $slug = $this->uniqueSlug(
            (string) ($data['slug'] ?? '') !== '' ? (string) $data['slug'] : (string) $data['name'],
            $category,
        );

        $category->update([
            'parent_id' => $parentId,
            'name' => $data['name'],
            'slug' => $slug,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    /**
     * @throws RuntimeException
     */
    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new RuntimeException('Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        if ($category->children()->exists()) {
            throw new RuntimeException('Kategori tidak dapat dihapus karena masih memiliki subkategori.');
        }

        DB::transaction(fn (): ?bool => $category->delete());
    }

    private function uniqueSlug(string $value, ?Category $ignore = null): string
    {
        $base = Str::slug($value) ?: 'kategori';
        $slug = $base;
        $suffix = 1;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
