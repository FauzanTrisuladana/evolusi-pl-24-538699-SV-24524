<!-- Confirm Modal Component -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-danger text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <strong>Perhatian!</strong> {{ $message }}
                </div>
                <form id="{{ $formId }}" method="POST" action="{{ $formAction }}">
                    @csrf
                    @if($method && $method !== 'POST')
                        @method($method)
                    @endif
                    @if($requireConfirm)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="{{ $checkboxId }}" name="confirm" value="on">
                        <label class="form-check-label" for="{{ $checkboxId }}">
                            {{ $confirmText }}
                        </label>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="{{ $formId }}" class="btn btn-danger" id="{{ $buttonId }}" @if($requireConfirm) disabled @endif>
                    <i class="bi bi-trash me-2"></i>
                    {{ $buttonText }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($requireConfirm)
        var checkbox = document.getElementById('{{ $checkboxId }}');
        var btn = document.getElementById('{{ $buttonId }}');
        if (checkbox && btn) {
            checkbox.addEventListener('change', function() {
                btn.disabled = !checkbox.checked;
            });
        }
        @endif
    });
</script>
