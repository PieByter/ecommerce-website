<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AdminProductService
{
    public function getProductsPaginated(?string $stockFilter, ?string $search = null): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'suppliers'])
            ->when($stockFilter === 'empty', function ($query): void {
                $query->where('stock', '<=', 0);
            })
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($cq) use ($search): void {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function getCategoriesAndSuppliers(): array
    {
        return [
            'categoryOptions' => Category::treeOptions(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ];
    }

    /**
     * @throws RuntimeException
     */
    public function storeProduct(array $validated, UploadedFile $uploadedImage): Product
    {
        $imagePath = $this->storeImageFile($uploadedImage);

        try {
            return DB::transaction(function () use ($validated, $imagePath): Product {
                $product = Product::query()->create([
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'weight' => $validated['weight'] ?? 0,
                    'image' => $imagePath,
                    'is_active' => (bool) $validated['is_active'],
                    'slug' => $this->generateUniqueSlug($validated['name']),
                ]);

                $product->suppliers()->sync($this->normalizeSupplierIds($validated));

                return $product;
            });
        } catch (Throwable $throwable) {
            $this->deleteStoredImageFile($imagePath);

            Log::error('Product creation failed.', [
                'message' => $throwable->getMessage(),
            ]);

            throw new RuntimeException('Produk gagal disimpan. Silakan coba lagi.');
        }
    }

    /**
     * @throws RuntimeException
     */
    public function updateProduct(Product $product, array $validated, ?UploadedFile $uploadedImage): void
    {
        $newImagePath = null;

        if ($uploadedImage instanceof UploadedFile) {
            $newImagePath = $this->storeImageFile($uploadedImage);
        }

        $oldImagePath = $product->image;

        try {
            DB::transaction(function () use ($product, $validated, $newImagePath): void {
                $payload = [
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'weight' => $validated['weight'] ?? 0,
                    'is_active' => (bool) $validated['is_active'],
                ];

                if ($newImagePath !== null) {
                    $payload['image'] = $newImagePath;
                }

                $product->update($payload);
                $product->suppliers()->sync($this->normalizeSupplierIds($validated));
            });
        } catch (Throwable $throwable) {
            if ($newImagePath !== null) {
                $this->deleteStoredImageFile($newImagePath);
            }

            Log::error('Product update failed.', [
                'product_id' => $product->getKey(),
                'message' => $throwable->getMessage(),
            ]);

            throw new RuntimeException('Produk gagal diperbarui. Silakan coba lagi.');
        }

        if ($newImagePath !== null) {
            $this->deleteStoredImageFile($oldImagePath);
        }
    }

    /**
     * @throws RuntimeException
     */
    public function deleteProduct(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new RuntimeException('Produk tidak dapat dihapus karena sudah digunakan pada pesanan.');
        }

        if ($product->purchaseOrderItems()->exists()) {
            throw new RuntimeException('Produk tidak dapat dihapus karena sudah digunakan pada purchase order.');
        }

        $imagePath = $product->image;

        try {
            DB::transaction(fn (): ?bool => $product->delete());
        } catch (Throwable $throwable) {
            Log::error('Product deletion failed.', [
                'product_id' => $product->getKey(),
                'message' => $throwable->getMessage(),
            ]);

            throw new RuntimeException('Produk gagal dihapus karena masih digunakan pada data lain.');
        }

        $this->deleteStoredImageFile($imagePath);
    }

    public function resolveUploadErrorMessage(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar. Maksimal 1MB.',
            UPLOAD_ERR_PARTIAL => 'Upload gambar tidak selesai. Silakan coba lagi.',
            UPLOAD_ERR_NO_FILE => 'File gambar wajib dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload tidak ditemukan di server.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menyimpan file upload.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP server.',
            default => 'Upload gambar gagal. Pastikan format JPG, JPEG, PNG, atau WEBP dan ukuran maksimal 1MB.',
        };
    }

    /**
     * @throws RuntimeException
     */
    private function storeImageFile(UploadedFile $file): string
    {
        $path = $file->store('products', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Upload gambar gagal. Coba gunakan file JPG/PNG berukuran maksimal 1MB.');
        }

        return $path;
    }

    private function deleteStoredImageFile(?string $imagePath): void
    {
        $path = trim((string) $imagePath);

        if ($path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        if (str_contains($path, '/storage/')) {
            $path = Str::after($path, '/storage/');
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $legacyPath = realpath(public_path($path));
        $publicRoot = realpath(public_path());

        if (
            is_string($legacyPath)
            && is_string($publicRoot)
            && is_file($legacyPath)
            && str_starts_with($legacyPath, $publicRoot.DIRECTORY_SEPARATOR)
        ) {
            @unlink($legacyPath);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function normalizeSupplierIds(array $validated): array
    {
        return collect(Arr::wrap($validated['supplier_ids'] ?? []))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produk';

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (Product::query()->where('slug', $slug)->exists());

        return $slug;
    }
}
