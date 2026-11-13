<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): View
    {
        $cartItems = $this->cartService->getCartItems($request->user());

        return view('customer.cart', compact('cartItems'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $this->cartService->addItem(
            $request->user(),
            $product,
            (int) ($validated['quantity'] ?? 1),
        );

        if ($result['status'] === 'error') {
            return back()->withErrors(['cart' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless((int) $cart->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $result = $this->cartService->updateItemQuantity($cart, (int) $validated['quantity']);

        if ($result['status'] === 'error') {
            return back()->withErrors(['cart' => $result['message']]);
        }

        return back();
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:increment,decrement,set'],
            'quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $result = $this->cartService->adjustQuantity(
            $request->user(),
            $product,
            $validated['action'],
            (int) ($validated['quantity'] ?? 0),
        );

        if (($result['status'] ?? null) === 'error') {
            return back()->withErrors(['cart' => $result['message']]);
        }

        return back()->with('success', $result['message'] ?? null);
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless((int) $cart->user_id === (int) $request->user()->id, 403);

        $this->cartService->removeItem($cart);

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
