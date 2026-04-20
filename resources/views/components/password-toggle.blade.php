@props([
    'id',
    'name',
    'label' => 'Password',
    'placeholder' => null,
    'autocomplete' => 'current-password',
    'required' => false,
    'error' => null,
])

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <div class="input-group">
        <input id="{{ $id }}" type="password" name="{{ $name }}" autocomplete="{{ $autocomplete }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            {{ $attributes->class(['form-control', 'is-invalid' => $error]) }}>

        <button type="button" class="btn btn-outline-secondary js-password-toggle"
            data-password-target="{{ $id }}" aria-label="Toggle password visibility">
            <i class="bi bi-eye"></i>
        </button>
    </div>

    @if ($error)
        <div class="invalid-feedback d-block">{{ $error }}</div>
    @endif
</div>

@once
    <script>
        document.addEventListener('click', function(event) {
            const toggleButton = event.target.closest('.js-password-toggle');
            if (!toggleButton) {
                return;
            }

            const inputId = toggleButton.getAttribute('data-password-target');
            const passwordInput = document.getElementById(inputId);
            const icon = toggleButton.querySelector('i');

            if (!passwordInput || !icon) {
                return;
            }

            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        });
    </script>
@endonce
