@props([
    'product',
    'cartQuantity' => 0,
    'isOutOfStock' => false,
    'size' => 'sm',
])

@php
    $cartQuantity = (int) $cartQuantity;
    $quantityMax = max(1, (int) $product->stock);
    $buttonClass = $size === 'sm' ? 'btn-sm' : '';
@endphp

@if (auth()->check() && auth()->user()->isAdmin())
    <a href="{{ route('admin.products.edit', $product) }}"
        class="btn btn-outline-secondary {{ $buttonClass }}">Kelola Produk</a>
@elseif (auth()->check())
    <div class="d-flex align-items-center justify-content-center gap-2">
        <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
            @csrf
            <input type="hidden" name="action" value="decrement">
            <button type="submit" class="btn btn-outline-secondary {{ $buttonClass }}"
                @disabled($isOutOfStock || $cartQuantity <= 0)>-</button>
        </form>

        <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
            @csrf
            <input type="hidden" name="action" value="set">
            <input type="number" name="quantity" class="form-control {{ $size === 'sm' ? 'form-control-sm' : '' }} text-center"
                value="{{ $cartQuantity }}" min="0" max="{{ $quantityMax }}"
                style="width: {{ $size === 'sm' ? '88px' : '96px' }};" aria-label="Jumlah item di keranjang"
                onchange="this.form.requestSubmit()" @disabled($isOutOfStock)>
        </form>

        <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
            @csrf
            <input type="hidden" name="action" value="increment">
            <button type="submit" class="btn btn-danger {{ $buttonClass }}" @disabled($isOutOfStock)>+</button>
        </form>
    </div>
@else
    <a href="{{ route('login') }}" class="btn btn-danger {{ $buttonClass }}">Login untuk Membeli</a>
@endif
