<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    /**
     * Statuses that count as paid revenue.
     *
     * @var array<int, string>
     */
    public const PAID_STATUSES = [
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
    ];

    /**
     * Allowed status transitions for the admin order screen.
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_price',
        'shipping_cost',
        'shipping_address',
        'courier',
        'courier_service',
        'tracking_number',
        'payment_proof',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelFor((string) $this->status);
    }

    public static function labelFor(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_DELIVERED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::badgeClassFor((string) $this->status);
    }

    public static function badgeClassFor(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'text-warning-emphasis bg-warning-subtle',
            self::STATUS_CONFIRMED => 'text-primary-emphasis bg-primary-subtle',
            self::STATUS_PROCESSING => 'text-info-emphasis bg-info-subtle',
            self::STATUS_SHIPPED => 'text-secondary-emphasis bg-secondary-subtle',
            self::STATUS_DELIVERED => 'text-success-emphasis bg-success-subtle',
            self::STATUS_CANCELLED => 'text-danger-emphasis bg-danger-subtle',
            default => 'text-bg-secondary',
        };
    }

    public function getGrandTotalAttribute(): float
    {
        return (float) $this->total_price + (float) $this->shipping_cost;
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        $path = trim((string) $this->payment_proof);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * @return array<int, string>
     */
    public function allowedTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canTransitionTo(string $status): bool
    {
        return $status === $this->status || in_array($status, $this->allowedTransitions(), true);
    }

    public function canBeCancelled(): bool
    {
        return in_array(self::STATUS_CANCELLED, $this->allowedTransitions(), true);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, self::PAID_STATUSES, true);
    }
}
