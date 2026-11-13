@props([
    'categories',
    'selectedCategoryId' => null,
    'routeName',
    'keyword' => null,
])

@php
    $selectedId = (int) ($selectedCategoryId ?? 0);
    $selectedCategory = $selectedId > 0 ? $categories->firstWhere('id', $selectedId) : null;
    $selectedRootId = $selectedCategory?->parent_id ?? $selectedCategory?->id;
    $roots = $categories->filter(fn($category): bool => $category->parent_id === null)->values();
    $quickRoots = $roots->take(9);
    $otherRoots = $roots->skip(9);
    $selectedRoot = $selectedRootId ? $categories->firstWhere('id', (int) $selectedRootId) : null;
    $childCategories = $selectedRoot
        ? $categories->where('parent_id', (int) $selectedRoot->id)->values()
        : collect();

    $routeParameters = function (array $parameters = []) use ($keyword): array {
        if (filled($keyword)) {
            $parameters['q'] = $keyword;
        }

        return $parameters;
    };
@endphp

<div class="mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="small text-muted">Filter kategori:</span>
        <a href="{{ route($routeName, $routeParameters()) }}"
            class="badge rounded-pill text-decoration-none px-3 py-2 {{ $selectedId === 0 ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
            Semua
        </a>

        @foreach ($quickRoots as $root)
            <a href="{{ route($routeName, $routeParameters(['category' => $root->id])) }}"
                class="badge rounded-pill text-decoration-none px-3 py-2 {{ (int) $selectedRootId === (int) $root->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                {{ $root->name }}
            </a>
        @endforeach

        @if ($otherRoots->isNotEmpty())
            <div class="dropdown">
                <button class="badge rounded-pill text-bg-light border text-dark px-3 py-2 dropdown-toggle"
                    style="cursor: pointer;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    +{{ $otherRoots->count() }} lainnya
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm p-3 border-0"
                    style="width: min(500px, 92vw); max-height: min(500px, 60vh); overflow: auto;">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($otherRoots as $root)
                            <a href="{{ route($routeName, $routeParameters(['category' => $root->id])) }}"
                                class="badge rounded-pill text-decoration-none px-3 py-2 {{ (int) $selectedRootId === (int) $root->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                                {{ $root->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($roots->isEmpty())
            <span class="text-muted small">Belum ada kategori.</span>
        @endif
    </div>

    @if ($childCategories->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 align-items-center mt-2 ps-0 ps-md-4">
            <span class="small text-muted">Subkategori {{ $selectedRoot?->name }}:</span>
            @foreach ($childCategories as $child)
                <a href="{{ route($routeName, $routeParameters(['category' => $child->id])) }}"
                    class="badge rounded-pill text-decoration-none px-3 py-2 {{ $selectedId === (int) $child->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                    {{ $child->name }}
                </a>
            @endforeach
        </div>
    @endif
</div>
