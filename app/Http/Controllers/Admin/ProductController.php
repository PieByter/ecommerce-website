<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AdminProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ProductController extends Controller
{
    public function __construct(private readonly AdminProductService $productService) {}

    public function index(Request $request): View
    {
        $products = $this->productService->getProductsPaginated(
            $request->string('stock')->toString() ?: null,
            $request->string('search')->toString() ?: null,
        );

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $data = $this->productService->getCategoriesAndSuppliers();

        return view('admin.products.create', [
            'categories' => $data['categories'],
            'suppliers' => $data['suppliers'],
        ]);
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
                ->withErrors(['image_file' => $this->productService->resolveUploadErrorMessage($uploadedImage)]);
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
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        try {
            $this->productService->storeProduct($validated, $uploadedImage);
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['image_file' => $exception->getMessage()]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $data = $this->productService->getCategoriesAndSuppliers();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $data['categories'],
            'suppliers' => $data['suppliers'],
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $uploadedImage = $request->file('image_file');

        if ($request->files->has('image_file') && (! $uploadedImage instanceof UploadedFile || ! $uploadedImage->isValid())) {
            return back()
                ->withInput()
                ->withErrors([
                    'image_file' => $uploadedImage instanceof UploadedFile
                        ? $this->productService->resolveUploadErrorMessage($uploadedImage)
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
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        try {
            $this->productService->updateProduct($product, $validated, $uploadedImage);
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['image_file' => $exception->getMessage()]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
