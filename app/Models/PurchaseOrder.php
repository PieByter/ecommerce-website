<?php

namespace App\Models;

use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ORDERED,
        self::STATUS_RECEIVED,
        self::STATUS_CANCELLED,
    ];

    /**
     * Allowed status transitions. Received is handled by the receive action only.
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSITIONS = [
        self::STATUS_DRAFT => [self::STATUS_ORDERED, self::STATUS_CANCELLED],
        self::STATUS_ORDERED => [self::STATUS_CANCELLED],
        self::STATUS_RECEIVED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'supplier_id',
        'po_number',
        'status',
        'order_date',
        'expected_date',
        'total_cost',
        'notes',
        'received_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_at' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelFor((string) $this->status);
    }

    public static function labelFor(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ORDERED => 'Dipesan',
            self::STATUS_RECEIVED => 'Diterima',
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
            self::STATUS_DRAFT => 'text-secondary-emphasis bg-secondary-subtle',
            self::STATUS_ORDERED => 'text-primary-emphasis bg-primary-subtle',
            self::STATUS_RECEIVED => 'text-success-emphasis bg-success-subtle',
            self::STATUS_CANCELLED => 'text-danger-emphasis bg-danger-subtle',
            default => 'text-bg-secondary',
        };
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

    public function canBeReceived(): bool
    {
        return $this->status === self::STATUS_ORDERED;
    }

    public function canBeDeleted(): bool
    {
        return $this->status !== self::STATUS_RECEIVED;
    }
}
