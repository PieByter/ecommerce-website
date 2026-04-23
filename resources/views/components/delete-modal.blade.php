@props([
    'id',           // e.g. 'deleteProductModal'
    'formId',       // e.g. 'deleteProductForm'
    'nameTargetId', // e.g. 'deleteProductName'
    'confirmBtnId', // e.g. 'confirmDeleteProductBtn'
    'label',        // e.g. 'Produk'
    'deleteRoute' => null, // optional: static delete route
])

<form id="{{ $formId }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    @if ($deleteRoute)
        <input type="hidden" name="_delete_route" value="{{ $deleteRoute }}">
    @endif
</form>

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="{{ $id }}Label">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $label }} <strong id="{{ $nameTargetId }}"></strong> akan dihapus permanen.
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="{{ $confirmBtnId }}">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteModal  = document.getElementById('{{ $id }}');
        const deleteForm   = document.getElementById('{{ $formId }}');
        const nameTarget   = document.getElementById('{{ $nameTargetId }}');
        const confirmBtn   = document.getElementById('{{ $confirmBtnId }}');

        deleteModal.addEventListener('show.bs.modal', function (event) {
            const trigger   = event.relatedTarget;
            const deleteUrl = trigger?.getAttribute('data-delete-url') ?? '';
            const deleteName = trigger?.getAttribute('data-delete-name') ?? 'item ini';

            deleteForm.setAttribute('action', deleteUrl);
            nameTarget.textContent = deleteName;
        });

        confirmBtn.addEventListener('click', function () {
            deleteForm.submit();
        });
    });
</script>
