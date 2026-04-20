<div wire:poll.30s>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#0ea5e9,#0284c7);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Pembeli</div>
                        <div class="stat-value">{{ number_format($stats['totalCustomers']) }}</div>
                    </div>
                    <i class="bi bi-people-fill stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#22c55e,#16a34a);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-value">{{ number_format($stats['totalProducts']) }}</div>
                    </div>
                    <i class="bi bi-box-seam-fill stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#f97316,#ea580c);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Pesanan</div>
                        <div class="stat-value">{{ number_format($stats['totalOrders']) }}</div>
                    </div>
                    <i class="bi bi-receipt-cutoff stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg,#8b5cf6,#7c3aed);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Omzet (Paid)</div>
                        <div class="stat-value fs-4">Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}</div>
                    </div>
                    <i class="bi bi-cash-coin stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-clock-history"></i>
        <div>Pesanan pending saat ini: <strong>{{ number_format($stats['pendingOrders']) }}</strong></div>
    </div>
</div>
