<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function index(Request $request): View|RedirectResponse
    {
        $details = $this->checkoutService->getCheckoutDetails($request->user());

        if ($details['cartItems']->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang kosong. Tambahkan produk terlebih dahulu.']);
        }

        if ($details['hasUnavailableItems']) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Ada produk di keranjang yang stoknya tidak mencukupi. Silakan sesuaikan jumlahnya terlebih dahulu.',
            ]);
        }

        return view('customer.checkout', [
            'cartItems' => $details['cartItems'],
            'subtotal' => $details['subtotal'],
            'shippingCost' => $details['shippingCost'],
            'grandTotal' => $details['grandTotal'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
            'courier' => ['nullable', 'string', 'max:255'],
            'courier_service' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $order = $this->checkoutService->processCheckout($request->user(), $validated);
        } catch (RuntimeException $exception) {
            $route = $exception->getMessage() === 'Keranjang kosong.' ? 'cart.index' : 'checkout.index';

            return redirect()->route($route)->withErrors(['cart' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Checkout berhasil. Pesanan '.$order->order_number.' sedang menunggu konfirmasi admin.');
    }
}
