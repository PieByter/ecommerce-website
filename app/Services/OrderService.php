<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class OrderService
{
    /**
     * Update an order status using the allowed transition map.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RuntimeException
     */
    public function updateStatus(Order $order, array $data): void
    {
        DB::transaction(function () use ($order, $data): void {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->with('items')
                ->lockForUpdate()
                ->firstOrFail();

            $nextStatus = (string) $data['status'];

            if (! $lockedOrder->canTransitionTo($nextStatus)) {
                throw new RuntimeException(sprintf(
                    'Status pesanan tidak dapat diubah dari %s ke %s.',
                    $lockedOrder->status_label,
                    Order::labelFor($nextStatus),
                ));
            }

            $payload = $data;

            if ($nextStatus === Order::STATUS_CANCELLED && $lockedOrder->status !== Order::STATUS_CANCELLED) {
                $this->restoreStock($lockedOrder);
            }

            if ($nextStatus === Order::STATUS_CONFIRMED && $lockedOrder->paid_at === null) {
                $payload['paid_at'] = now();
            }

            $lockedOrder->update($payload);
        });
    }

    /**
     * Cancel a pending order owned by the given customer.
     *
     * @throws ModelNotFoundException
     * @throws RuntimeException
     */
    public function cancelForCustomer(User $user, Order $order): void
    {
        DB::transaction(function () use ($user, $order): void {
            $lockedOrder = Order::query()
                ->whereBelongsTo($user)
                ->whereKey($order->getKey())
                ->with('items')
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                throw (new ModelNotFoundException)->setModel(Order::class, [$order->getKey()]);
            }

            if ($lockedOrder->status !== Order::STATUS_PENDING) {
                throw new RuntimeException('Pesanan hanya bisa dibatalkan saat status masih menunggu konfirmasi.');
            }

            $this->restoreStock($lockedOrder);
            $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);
        });
    }

    /**
     * Store a payment proof and mark the order as paid (still awaiting admin confirmation).
     *
     * @throws RuntimeException
     */
    public function uploadPaymentProof(Order $order, UploadedFile $file): void
    {
        if ($order->status !== Order::STATUS_PENDING) {
            throw new RuntimeException('Bukti pembayaran hanya dapat diunggah saat pesanan masih menunggu konfirmasi.');
        }

        $newPath = $file->store('payments', 'public');

        if (! is_string($newPath) || $newPath === '') {
            throw new RuntimeException('Bukti pembayaran gagal diunggah. Silakan coba lagi.');
        }

        $oldPath = $order->payment_proof;

        try {
            DB::transaction(function () use ($order, $newPath): void {
                $order->update([
                    'payment_proof' => $newPath,
                    'paid_at' => now(),
                ]);
            });
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete($newPath);

            Log::error('Payment proof upload failed.', [
                'order_id' => $order->getKey(),
                'message' => $throwable->getMessage(),
            ]);

            throw new RuntimeException('Bukti pembayaran gagal disimpan. Silakan coba lagi.');
        }

        $this->deleteStoredPaymentProof($oldPath);
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            Product::query()
                ->whereKey($item->product_id)
                ->increment('stock', (int) $item->quantity);
        }
    }

    private function deleteStoredPaymentProof(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        Storage::disk('public')->delete($path);
    }
}
