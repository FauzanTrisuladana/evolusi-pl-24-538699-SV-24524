<!-- Create Link Modal -->
<div class="modal fade" id="createLinkModal" tabindex="-1" aria-labelledby="createLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="createLinkModalLabel">
                    <i class="bi bi-link-45deg me-2"></i>
                    Buat Link Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createLinkForm" method="POST" action="{{ route('links.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="originalUrl" class="form-label">Original URL</label>
                        <input
                            type="url"
                            class="form-control @error('target_url', 'new') is-invalid @enderror"
                            id="originalUrl"
                            name="target_url"
                            placeholder="https://example.com/very-long-url"
                            value="{{ old('target_url') }}"
                            required>
                        @error('target_url', 'new')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="linkName" class="form-label">Nama Link</label>
                        <input
                            type="text"
                            class="form-control @error('name', 'new') is-invalid @enderror"
                            id="linkName"
                            name="name"
                            placeholder="My Campaign"
                            value="{{ old('name') }}"
                            required>
                        @error('name', 'new')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="customAlias" class="form-label">Alias Kustom (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ rtrim(config('app.url'), '/') . '/' }}</span>
                            <input
                                type="text"
                                class="form-control @error('custom_alias', 'new') is-invalid @enderror"
                                id="customAlias"
                                name="custom_alias"
                                placeholder="my-link"
                                value="{{ old('custom_alias') }}">
                        </div>
                        @error('custom_alias', 'new')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kosongkan untuk alias acak</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="createLinkStatus"
                                name="is_active"
                                value="1"
                                {{ $errors->hasBag('new') ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                            <label class="form-check-label" for="createLinkStatus">
                                Aktif
                            </label>
                        </div>
                        @error('is_active', 'new')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="createLinkForm" class="btn btn-danger">
                    <i class="bi bi-check-circle me-2"></i>
                    Buat Link
                </button>
            </div>
        </div>
    </div>
</div>
