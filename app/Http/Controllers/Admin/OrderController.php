<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with('user')
            ->when(
                $request->filled('status') && in_array($request->string('status')->toString(), Order::STATUSES, true),
                fn ($query) => $query->where('status', $request->string('status')->toString()),
            )
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(function ($q) use ($search): void {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search): void {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.product']);

        $nextStatuses = array_values(array_unique(array_merge([$order->status], $order->allowedTransitions())));

        return view('admin.orders.show', compact('order', 'nextStatuses'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'courier' => ['nullable', 'string', 'max:255'],
            'courier_service' => ['nullable', 'string', 'max:255'],
            'tracking_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($request->input('status') === Order::STATUS_SHIPPED),
            ],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'tracking_number.required' => 'Nomor resi wajib diisi ketika pesanan dikirim.',
        ]);

        $validated['shipping_cost'] = (float) ($validated['shipping_cost'] ?? $order->shipping_cost);

        try {
            $this->orderService->updateStatus($order, $validated);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
