@props(['colspan', 'message' => 'Belum ada data.'])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-4 text-muted">
        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
        {{ $message }}
    </td>
</tr>
