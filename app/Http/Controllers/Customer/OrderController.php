<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $order->load(['items.product']);

        return view('customer.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        try {
            $this->orderService->cancelForCustomer($request->user(), $order);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('customer.orders.show', $order)
                ->withErrors(['order' => $exception->getMessage()]);
        }

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function uploadPaymentProof(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $file = $validated['payment_proof'];

        if (! $file instanceof UploadedFile) {
            return back()->withErrors(['payment_proof' => 'File bukti pembayaran tidak valid.']);
        }

        try {
            $this->orderService->uploadPaymentProof($order, $file);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['payment_proof' => $exception->getMessage()]);
        }

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
    }
}
