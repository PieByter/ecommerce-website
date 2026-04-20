<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'supplier'])
            ->when($request->string('stock')->toString() === 'empty', function ($query): void {
                $query->where('stock', '<=', 0);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $suppliers = Supplier::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $uploadedImage = $request->file('image_file');

        if (! $uploadedImage instanceof UploadedFile) {
            return back()
                ->withInput()
                ->withErrors(['image_file' => 'File gambar wajib dipilih.']);
        }

        if (! $uploadedImage->isValid()) {
            return back()
                ->withInput()
                ->withErrors(['image_file' => $this->resolveUploadErrorMessage($uploadedImage)]);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'image_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $imagePath = $this->storeImageFile($uploadedImage);
        } catch (\Throwable $throwable) {
            Log::error('Product image upload failed during store.', [
                'message' => $throwable->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['image_file' => 'Upload gambar gagal. Coba gunakan file JPG/PNG berukuran maksimal 1MB.']);
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
            'is_active' => $request->boolean('is_active', true),
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
        ];

        Product::query()->create($productPayload);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $suppliers = Supplier::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $uploadedImage = $request->file('image_file');

        if ($request->files->has('image_file') && (! $uploadedImage instanceof UploadedFile || ! $uploadedImage->isValid())) {
            return back()
                ->withInput()
                ->withErrors([
                    'image_file' => $uploadedImage instanceof UploadedFile
                        ? $this->resolveUploadErrorMessage($uploadedImage)
                        : 'File gambar tidak valid. Silakan pilih ulang file gambar.',
                ]);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'is_active' => ['nullable', 'boolean'],
        ]);

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

                return back()
                    ->withInput()
                    ->withErrors(['image_file' => 'Upload gambar gagal. Coba gunakan file JPG/PNG berukuran maksimal 1MB.']);
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
            'is_active' => $request->boolean('is_active', true),
            'slug' => Str::slug($validated['name']) . '-' . $product->id,
        ];

        $product->update($productPayload);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteStoredImageFile($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function storeImageFile(UploadedFile $file): string
    {
        // Menyimpan file mematuhi storage public (public/storage/images)
        $path = $file->store('images', 'public');

        if (! $path) {
            throw new \RuntimeException('Unable to store image file.');
        }

        // Return file URL path that matches public/storage/images
        return 'storage/' . $path;
    }

    private function resolveUploadErrorMessage(UploadedFile $file): string
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

    private function deleteStoredImageFile(?string $imagePath): void
    {
        if (! is_string($imagePath) || $imagePath === '') {
            return;
        }

        if (str_starts_with($imagePath, 'storage/')) {
            $relativePath = substr($imagePath, 8); // hapus "storage/" di depan
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
            // Backward compatibility for old records saved before path migration.
            $relativePath = ltrim(Str::after($path, '/products/'), '/');
            $baseDirectory = 'products';
        } else {
            return;
        }

        if (! is_string($relativePath) || $relativePath === '') {
            return;
        }

        $fullPath = public_path($baseDirectory . '/' . $relativePath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
