<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $cartItems = $user->carts()->with('product')->latest()->get();

        return view('customer.cart', compact('cartItems'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $product->is_active || $product->stock < 1) {
            return back()->withErrors(['cart' => 'Produk tidak tersedia untuk ditambahkan ke keranjang.']);
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedQuantity = (int) ($validated['quantity'] ?? 1);

        $cartItem = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $currentQuantity = (int) ($cartItem->quantity ?? 0);
        $nextQuantity = min($currentQuantity + $requestedQuantity, (int) $product->stock);

        $cartItem->quantity = max(1, $nextQuantity);
        $cartItem->save();

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($cart->user_id === $user->id, 403);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $maxQuantity = max(1, (int) ($cart->product?->stock ?? $validated['quantity']));
        $nextQuantity = min((int) $validated['quantity'], $maxQuantity);

        $cart->update(['quantity' => $nextQuantity]);

        return back();
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:increment,decrement,set'],
            'quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $cartItem = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $currentQuantity = (int) ($cartItem->quantity ?? 0);
        $action = $validated['action'];

        if ($action === 'set') {
            $requestedQuantity = (int) ($validated['quantity'] ?? 0);

            if ($requestedQuantity <= 0) {
                if ($cartItem->exists) {
                    $cartItem->delete();
                }

                return back();
            }

            if (! $product->is_active || $product->stock < 1) {
                return back()->withErrors(['cart' => 'Produk tidak tersedia untuk ditambahkan ke keranjang.']);
            }

            $nextQuantity = min($requestedQuantity, (int) $product->stock);
            $cartItem->quantity = max(1, $nextQuantity);
            $cartItem->save();

            return back();
        }

        if ($action === 'increment') {
            if (! $product->is_active || $product->stock < 1) {
                return back()->withErrors(['cart' => 'Produk tidak tersedia untuk ditambahkan ke keranjang.']);
            }

            $nextQuantity = min($currentQuantity + 1, (int) $product->stock);
            $cartItem->quantity = max(1, $nextQuantity);
            $cartItem->save();

            return back();
        }

        if ($currentQuantity <= 1) {
            if ($cartItem->exists) {
                $cartItem->delete();
            }

            return back();
        }

        $cartItem->quantity = $currentQuantity - 1;
        $cartItem->save();

        return back();
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($cart->user_id === $user->id, 403);

        $cart->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
