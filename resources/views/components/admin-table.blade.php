@props([
    'columns',       // array of column labels, e.g. ['No', 'Nama', 'Aksi']
    'searchRoute' => null,   // named route for search form
    'searchName'  => 'search', // query param name
    'searchPlaceholder' => 'Cari...',
    'filterSlot' => false,   // enable extra filter slot
    'emptyText' => 'Belum ada data.',
    'emptyColspan' => null,
])

@php $colspan = $emptyColspan ?? count($columns); @endphp

<div class="card">
    {{-- Optional search bar / filter area --}}
    @if ($searchRoute || $filterSlot)
        <div class="card-body border-bottom py-2">
            <div class="row g-2 align-items-end">
                @if ($searchRoute)
                    <div class="col-md-4">
                        <form method="GET" action="{{ route($searchRoute) }}" class="d-flex gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    name="{{ $searchName }}"
                                    value="{{ request($searchName) }}"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="{{ $searchPlaceholder }}"
                                >
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                            @if (request($searchName))
                                <a href="{{ route($searchRoute) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                @endif
                @if ($filterSlot)
                    <div class="col">
                        {{ $filter }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr class="text-center align-middle">
                    @foreach ($columns as $col)
                        <th>{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
