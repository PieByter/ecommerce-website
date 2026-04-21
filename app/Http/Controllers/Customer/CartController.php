<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $cartItems = $this->cartService->getCartItems($user);

        return view('customer.cart', compact('cartItems'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedQuantity = (int) ($validated['quantity'] ?? 1);

        $result = $this->cartService->addItem($user, $product, $requestedQuantity);

        if ($result['status'] === 'error') {
            return back()->withErrors(['cart' => $result['message']]);
        }

        return back()->with('success', $result['message']);
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

        $this->cartService->updateItemQuantity($cart, (int) $validated['quantity']);

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

        $result = $this->cartService->adjustQuantity($user, $product, $validated['action'], (int) ($validated['quantity'] ?? 0));

        if (isset($result['status']) && $result['status'] === 'error') {
            return back()->withErrors(['cart' => $result['message']]);
        }

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

        $this->cartService->removeItem($cart);

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
