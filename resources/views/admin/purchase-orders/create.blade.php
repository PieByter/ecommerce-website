@extends('layouts.admin')

@section('title', 'Buat Purchase Order')
@section('page_title', 'Buat Purchase Order')

@section('content')
    @php
        $initialItems = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => 0]]);
    @endphp

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.purchase-orders.store') }}" class="row g-3" id="purchaseOrderForm">
                @csrf

                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select" required>
                        <option value="">Pilih supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((int) old('supplier_id') === (int) $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tanggal PO</label>
                    <input type="date" name="order_date" class="form-control"
                        value="{{ old('order_date', now()->toDateString()) }}" required>
                    @error('order_date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Estimasi Datang</label>
                    <input type="date" name="expected_date" class="form-control" value="{{ old('expected_date') }}">
                    @error('expected_date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status Awal</label>
                    <select name="status" class="form-select" required>
                        <option value="draft" @selected(old('status', 'ordered') === 'draft')>Draft</option>
                        <option value="ordered" @selected(old('status', 'ordered') === 'ordered')>Ordered</option>
                    </select>
                    @error('status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Catatan untuk supplier (opsional)">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">Item Produk</h2>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Item
                        </button>
                    </div>

                    @error('items')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror

                    <div class="table-responsive">
                        <table class="table align-middle" id="itemsTable">
                            <thead>
                                <tr class="text-center">
                                    <th style="min-width: 260px;">Produk</th>
                                    <th style="width: 130px;">Qty</th>
                                    <th style="width: 200px;">Harga Beli</th>
                                    <th style="width: 200px;">Subtotal</th>
                                    <th style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($initialItems as $index => $item)
                                    <tr class="po-item-row">
                                        <td>
                                            <select name="items[{{ $index }}][product_id]"
                                                class="form-select item-product" required>
                                                <option value="">Pilih produk</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-supplier-id="{{ $product->supplier_id ?? '' }}"
                                                        @selected((int) ($item['product_id'] ?? 0) === (int) $product->id)>
                                                        {{ $product->name }}
                                                        {{ $product->supplier?->name ? ' - ' . $product->supplier->name : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('items.' . $index . '.product_id')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]"
                                                class="form-control text-center item-quantity"
                                                value="{{ (int) ($item['quantity'] ?? 1) }}" min="1" required>
                                            @error('items.' . $index . '.quantity')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][unit_cost]"
                                                class="form-control item-unit-cost"
                                                value="{{ (float) ($item['unit_cost'] ?? 0) }}" min="0"
                                                step="0.01" required>
                                            @error('items.' . $index . '.unit_cost')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text" class="form-control item-line-total text-end"
                                                value="Rp 0" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"
                                                title="Hapus item" aria-label="Hapus item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">Total PO</td>
                                    <td>
                                        <input type="text" class="form-control text-end fw-bold" id="grandTotalDisplay"
                                            value="Rp 0" readonly>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-danger">Simpan PO</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const supplierSelect = document.getElementById('supplier_id');
            const addItemButton = document.getElementById('addItemBtn');
            const itemsTableBody = document.querySelector('#itemsTable tbody');
            const grandTotalDisplay = document.getElementById('grandTotalDisplay');

            const formatCurrency = (value) => {
                return new Intl.NumberFormat('id-ID').format(Math.max(0, Number(value) || 0));
            };

            const calculateTotals = () => {
                let grandTotal = 0;

                itemsTableBody.querySelectorAll('tr.po-item-row').forEach((row) => {
                    const quantityInput = row.querySelector('.item-quantity');
                    const unitCostInput = row.querySelector('.item-unit-cost');
                    const lineTotalInput = row.querySelector('.item-line-total');

                    const quantity = Number(quantityInput?.value || 0);
                    const unitCost = Number(unitCostInput?.value || 0);
                    const lineTotal = quantity * unitCost;

                    grandTotal += lineTotal;
                    lineTotalInput.value = 'Rp ' + formatCurrency(lineTotal);
                });

                grandTotalDisplay.value = 'Rp ' + formatCurrency(grandTotal);
            };

            const syncProductBySupplier = () => {
                const supplierId = supplierSelect.value;

                itemsTableBody.querySelectorAll('.item-product').forEach((selectElement) => {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];

                    Array.from(selectElement.options).forEach((option) => {
                        if (!option.value) {
                            option.hidden = false;
                            return;
                        }

                        const optionSupplierId = option.getAttribute('data-supplier-id') || '';
                        option.hidden = supplierId !== '' && optionSupplierId !== supplierId;
                    });

                    if (selectedOption && selectedOption.hidden) {
                        selectElement.value = '';
                    }
                });
            };

            const reindexRows = () => {
                itemsTableBody.querySelectorAll('tr.po-item-row').forEach((row, index) => {
                    const product = row.querySelector('.item-product');
                    const quantity = row.querySelector('.item-quantity');
                    const unitCost = row.querySelector('.item-unit-cost');

                    product.name = `items[${index}][product_id]`;
                    quantity.name = `items[${index}][quantity]`;
                    unitCost.name = `items[${index}][unit_cost]`;
                });
            };

            const createItemRow = () => {
                const row = document.createElement('tr');
                row.className = 'po-item-row';

                const options = Array.from(itemsTableBody.querySelector('.item-product').options)
                    .map((option) => option.cloneNode(true));

                const productSelect = document.createElement('select');
                productSelect.className = 'form-select item-product';
                productSelect.required = true;
                options.forEach((option) => productSelect.appendChild(option));
                productSelect.value = '';

                row.innerHTML = `
                    <td></td>
                    <td><input type="number" class="form-control text-center item-quantity" value="1" min="1" required></td>
                    <td><input type="number" class="form-control item-unit-cost" value="0" min="0" step="0.01" required></td>
                    <td><input type="text" class="form-control item-line-total text-end" value="Rp 0" readonly></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Hapus item" aria-label="Hapus item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                row.children[0].appendChild(productSelect);

                itemsTableBody.appendChild(row);
                reindexRows();
                syncProductBySupplier();
                calculateTotals();
            };

            addItemButton.addEventListener('click', function() {
                createItemRow();
            });

            supplierSelect.addEventListener('change', function() {
                syncProductBySupplier();
            });

            itemsTableBody.addEventListener('input', function(event) {
                if (event.target.classList.contains('item-quantity') || event.target.classList.contains(
                        'item-unit-cost')) {
                    calculateTotals();
                }
            });

            itemsTableBody.addEventListener('click', function(event) {
                const button = event.target.closest('.remove-item-btn');
                if (!button) {
                    return;
                }

                if (itemsTableBody.querySelectorAll('tr.po-item-row').length === 1) {
                    return;
                }

                button.closest('tr.po-item-row').remove();
                reindexRows();
                calculateTotals();
            });

            reindexRows();
            syncProductBySupplier();
            calculateTotals();
        });
    </script>
@endsection
