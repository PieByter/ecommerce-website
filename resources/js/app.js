const placeholderImage = '/images/placeholder.svg';

const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(Math.max(0, Number(value) || 0));

const initSupplierPickers = () => {
    document.querySelectorAll('[data-supplier-picker]').forEach((picker) => {
        const searchInput = picker.querySelector('[data-supplier-search]');
        const badgesContainer = picker.querySelector('[data-supplier-badges]');
        const select = picker.querySelector('[data-supplier-select]');
        const optionButtons = Array.from(picker.querySelectorAll('[data-supplier-option]'));

        if (!searchInput || !badgesContainer || !select || optionButtons.length === 0) {
            return;
        }

        const selectedIds = new Set(
            Array.from(select.options)
                .filter((option) => option.selected)
                .map((option) => Number(option.value)),
        );

        const syncSelect = () => {
            Array.from(select.options).forEach((option) => {
                option.selected = selectedIds.has(Number(option.value));
            });
        };

        const syncOptionStates = () => {
            const term = searchInput.value.trim().toLowerCase();

            optionButtons.forEach((button) => {
                const id = Number(button.dataset.supplierId);
                const name = button.dataset.supplierName || '';
                const isSelected = selectedIds.has(id);
                const matchesSearch = term === '' || name.includes(term);
                const label = button.querySelector('[data-supplier-option-label]');

                button.hidden = !matchesSearch && !isSelected;
                button.classList.toggle('active', isSelected);
                button.setAttribute('aria-pressed', String(isSelected));

                if (label) {
                    label.className = isSelected ? 'badge text-bg-success' : 'badge text-bg-light';
                    label.textContent = isSelected ? 'Dipilih' : 'Pilih';
                }
            });
        };

        const renderBadges = () => {
            badgesContainer.innerHTML = '';

            if (selectedIds.size === 0) {
                const emptyState = document.createElement('span');
                emptyState.className = 'text-muted small';
                emptyState.textContent = 'Belum ada supplier dipilih.';
                badgesContainer.appendChild(emptyState);
                return;
            }

            Array.from(select.options)
                .filter((option) => selectedIds.has(Number(option.value)))
                .forEach((option) => {
                    const badge = document.createElement('span');
                    badge.className = 'badge rounded-pill text-bg-danger d-inline-flex align-items-center gap-2 py-2 px-3';

                    const label = document.createElement('span');
                    label.textContent = option.text;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn-close btn-close-white btn-sm';
                    removeButton.style.fontSize = '0.65rem';
                    removeButton.setAttribute('aria-label', `Hapus ${option.text}`);
                    removeButton.addEventListener('click', () => {
                        selectedIds.delete(Number(option.value));
                        syncSelect();
                        renderBadges();
                        syncOptionStates();
                    });

                    badge.appendChild(label);
                    badge.appendChild(removeButton);
                    badgesContainer.appendChild(badge);
                });
        };

        optionButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const id = Number(button.dataset.supplierId);

                if (selectedIds.has(id)) {
                    selectedIds.delete(id);
                } else {
                    selectedIds.add(id);
                }

                syncSelect();
                renderBadges();
                syncOptionStates();
            });
        });

        searchInput.addEventListener('input', syncOptionStates);

        syncSelect();
        renderBadges();
        syncOptionStates();
    });
};

const initImagePreviews = () => {
    document.querySelectorAll('[data-image-input]').forEach((fileInput) => {
        const previewSelector = fileInput.dataset.imagePreview;
        const preview = previewSelector ? document.querySelector(previewSelector) : null;

        if (!preview) {
            return;
        }

        fileInput.addEventListener('change', (event) => {
            const file = event.target.files?.[0];

            if (!file) {
                preview.src = placeholderImage;
                return;
            }

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                preview.src = String(loadEvent.target?.result ?? placeholderImage);
            };
            reader.readAsDataURL(file);
        });

        preview.addEventListener('error', () => {
            preview.src = placeholderImage;
        });
    });
};

const initQuantityControls = () => {
    document.querySelectorAll('.js-qty-control').forEach((control) => {
        const container = control.closest('td');
        const input = control.querySelector('.js-qty-input');
        const decrementButton = control.querySelector('.js-qty-dec');
        const incrementButton = control.querySelector('.js-qty-inc');
        const form = container?.querySelector('.js-qty-form');
        const hiddenInput = container?.querySelector('.js-qty-hidden');

        if (!input || !decrementButton || !incrementButton || !form || !hiddenInput) {
            return;
        }

        const min = Number(control.dataset.min || '1');
        const max = Number(control.dataset.max || '999999');

        const submitQuantity = (nextValue) => {
            const clampedValue = Math.max(min, Math.min(max, Number(nextValue || min)));
            input.value = String(clampedValue);
            hiddenInput.value = String(clampedValue);
            form.submit();
        };

        decrementButton.addEventListener('click', () => submitQuantity(Number(input.value) - 1));
        incrementButton.addEventListener('click', () => submitQuantity(Number(input.value) + 1));
        input.addEventListener('change', () => submitQuantity(Number(input.value)));
    });
};

const initPurchaseOrderForm = () => {
    const form = document.getElementById('purchaseOrderForm');
    const supplierSelect = document.getElementById('supplier_id');
    const addItemButton = document.getElementById('addItemBtn');
    const itemsTableBody = document.querySelector('#itemsTable tbody');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    if (!form || !supplierSelect || !addItemButton || !itemsTableBody || !grandTotalDisplay) {
        return;
    }

    const calculateTotals = () => {
        let grandTotal = 0;

        itemsTableBody.querySelectorAll('tr.po-item-row').forEach((row) => {
            const quantity = Number(row.querySelector('.item-quantity')?.value || 0);
            const unitCost = Number(row.querySelector('.item-unit-cost')?.value || 0);
            const lineTotal = quantity * unitCost;

            grandTotal += lineTotal;

            const lineTotalInput = row.querySelector('.item-line-total');
            if (lineTotalInput) {
                lineTotalInput.value = 'Rp ' + formatCurrency(lineTotal);
            }
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

                const supplierIdList = (option.getAttribute('data-supplier-ids') || '').split(',');
                option.hidden = supplierId !== '' && !supplierIdList.includes(supplierId);
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

            if (product) product.name = `items[${index}][product_id]`;
            if (quantity) quantity.name = `items[${index}][quantity]`;
            if (unitCost) unitCost.name = `items[${index}][unit_cost]`;
        });
    };

    const createItemRow = () => {
        const sourceSelect = itemsTableBody.querySelector('.item-product');

        if (!sourceSelect) {
            return;
        }

        const row = document.createElement('tr');
        row.className = 'po-item-row';

        const productSelect = sourceSelect.cloneNode(false);
        productSelect.className = 'form-select item-product';
        productSelect.required = true;
        productSelect.value = '';

        Array.from(sourceSelect.options).forEach((option) => {
            const clone = option.cloneNode(true);
            clone.selected = false;
            productSelect.appendChild(clone);
        });

        const productCell = document.createElement('td');
        productCell.appendChild(productSelect);

        row.appendChild(productCell);
        row.insertAdjacentHTML('beforeend', `
            <td><input type="number" class="form-control text-center item-quantity" value="1" min="1" required></td>
            <td><input type="number" class="form-control item-unit-cost" value="0" min="0" step="0.01" required></td>
            <td><input type="text" class="form-control item-line-total text-end" value="Rp 0" readonly></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Hapus item" aria-label="Hapus item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `);

        itemsTableBody.appendChild(row);
        reindexRows();
        syncProductBySupplier();
        calculateTotals();
    };

    addItemButton.addEventListener('click', createItemRow);
    supplierSelect.addEventListener('change', syncProductBySupplier);

    itemsTableBody.addEventListener('input', (event) => {
        if (event.target.classList.contains('item-quantity') || event.target.classList.contains('item-unit-cost')) {
            calculateTotals();
        }
    });

    itemsTableBody.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-item-btn');

        if (!button || itemsTableBody.querySelectorAll('tr.po-item-row').length === 1) {
            return;
        }

        button.closest('tr.po-item-row')?.remove();
        reindexRows();
        calculateTotals();
    });

    reindexRows();
    syncProductBySupplier();
    calculateTotals();
};

const initPasswordToggles = () => {
    document.addEventListener('click', (event) => {
        const toggleButton = event.target.closest('.js-password-toggle');

        if (!toggleButton) {
            return;
        }

        const inputId = toggleButton.getAttribute('data-password-target');
        const passwordInput = inputId ? document.getElementById(inputId) : null;
        const icon = toggleButton.querySelector('i');

        if (!passwordInput || !icon) {
            return;
        }

        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !isPassword);
        icon.classList.toggle('bi-eye-slash', isPassword);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initSupplierPickers();
    initImagePreviews();
    initQuantityControls();
    initPurchaseOrderForm();
    initPasswordToggles();
});
