<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AdminProductService
{
    public function getProductsPaginated(?string $stockFilter)
    {
        return Product::query()
            ->with(['category', 'supplier'])
            ->when($stockFilter === 'empty', function ($query): void {
                $query->where('stock', '<=', 0);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function getCategoriesAndSuppliers(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ];
    }

    /**
     * @throws RuntimeException
     */
    public function storeProduct(array $validated, UploadedFile $uploadedImage): Product
    {
        try {
            $imagePath = $this->storeImageFile($uploadedImage);
        } catch (\Throwable $throwable) {
            Log::error('Product image upload failed during store.', [
                'message' => $throwable->getMessage(),
            ]);
            throw new RuntimeException('Upload gambar gagal. Coba gunakan file JPG/PNG berukuran maksimal 1MB.');
        }

        $productPayload = [
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'weight' => $validated['weight'] ?? 0,
            'image' => $imagePath,
            'is_active' => $validated['is_active'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(5),
        ];

        return Product::query()->create($productPayload);
    }

    /**
     * @throws RuntimeException
     */
    public function updateProduct(Product $product, array $validated, ?UploadedFile $uploadedImage): void
    {
        $imagePath = $product->image;

        if ($uploadedImage instanceof UploadedFile) {
            try {
                $this->deleteStoredImageFile($product->image);
                $imagePath = $this->storeImageFile($uploadedImage);
            } catch (\Throwable $throwable) {
                Log::error('Product image upload failed during update.', [
                    'product_id' => $product->id,
                    'message' => $throwable->getMessage(),
                ]);
                throw new RuntimeException('Upload gambar gagal. Coba gunakan file JPG/PNG berukuran maksimal 1MB.');
            }
        }

        $productPayload = [
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'weight' => $validated['weight'] ?? 0,
            'image' => $imagePath,
            'is_active' => $validated['is_active'],
            'slug' => Str::slug($validated['name']).'-'.$product->id,
        ];

        $product->update($productPayload);
    }

    public function deleteProduct(Product $product): void
    {
        $this->deleteStoredImageFile($product->image);
        $product->delete();
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

    private function storeImageFile(UploadedFile $file): string
    {
        $path = $file->store('images', 'public');

        if (! $path) {
            throw new RuntimeException('Unable to store image file.');
        }

        return 'storage/'.$path;
    }

    private function deleteStoredImageFile(?string $imagePath): void
    {
        if (! is_string($imagePath) || $imagePath === '') {
            return;
        }

        if (str_starts_with($imagePath, 'storage/')) {
            $relativePath = substr($imagePath, 8);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }

            return;
        }

        $path = parse_url($imagePath, PHP_URL_PATH);
        if (! is_string($path)) {
            return;
        }

        $relativePath = null;

        if (str_contains($path, '/uploads/products/')) {
            $relativePath = ltrim(Str::after($path, '/uploads/products/'), '/');
            $baseDirectory = 'uploads/products';
        } elseif (str_contains($path, '/products/')) {
            $relativePath = ltrim(Str::after($path, '/products/'), '/');
            $baseDirectory = 'products';
        } else {
            return;
        }

        if (! is_string($relativePath) || $relativePath === '') {
            return;
        }

        $fullPath = public_path($baseDirectory.'/'.$relativePath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
