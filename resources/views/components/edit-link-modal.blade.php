<!-- Edit Link Modal -->
<div class="modal fade" id="editLinkModal{{ $link->id_link }}" tabindex="-1" aria-labelledby="editLinkModalLabel{{ $link->id_link }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="editLinkModalLabel{{ $link->id_link }}">
                    <i class="bi bi-pencil me-2"></i>
                    Edit Link
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLinkForm{{ $link->id_link }}" method="POST" action="{{ route('links.update', ['id' => $link->id_link]) }}">
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="editOriginalUrl{{ $link->id_link }}" class="form-label">Original URL</label>
                        <input
                            type="url"
                            class="form-control @if(session('id')==$link->id_link && $errors->edit->has('target_url')) is-invalid @endif"
                            id="editOriginalUrl{{ $link->id_link }}"
                            name="target_url"
                            value="{{ $link->true_link }}"
                            placeholder="https://example.com/very-long-url"
                            required>
                        @if (session('id')==$link->id_link && $errors->edit->has('target_url'))
                            <div class="invalid-feedback d-block">{{ $errors->edit->first('target_url') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="editLinkName{{ $link->id_link }}" class="form-label">Nama Link</label>
                        <input
                            type="text"
                            class="form-control @if(session('id')==$link->id_link && $errors->edit->has('name')) is-invalid @endif"
                            id="editLinkName{{ $link->id_link }}"
                            name="name"
                            value="{{ $link->name }}"
                            placeholder="My Campaign"
                            required>
                        @if (session('id')==$link->id_link && $errors->edit->has('name'))
                            <div class="invalid-feedback d-block">{{ $errors->edit->first('name') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="editCustomAlias{{ $link->id_link }}" class="form-label">Alias Kustom</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ rtrim(config('app.url'), '/') . '/' }}</span>
                            <input
                                type="text"
                                class="form-control @if(session('id')==$link->id_link && $errors->edit->has('custom_alias')) is-invalid @endif"
                                id="editCustomAlias{{ $link->id_link }}"
                                name="custom_alias"
                                value="{{ $link->new_link }}"
                                placeholder="my-link"
                                required>
                        </div>
                        @if (session('id')==$link->id_link && $errors->edit->has('custom_alias'))
                            <div class="invalid-feedback d-block">{{ $errors->edit->first('custom_alias') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="editLinkStatus{{ $link->id_link }}"
                                name="is_active"
                                value="1"
                                {{ $link->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="editLinkStatus{{ $link->id_link }}">
                                Aktif
                            </label>
                        </div>
                        @if (session('id')==$link->id_link && $errors->edit->has('is_active'))
                            <div class="invalid-feedback d-block">{{ $errors->edit->first('is_active') }}</div>
                        @endif
                    </div>
                </form>
                @if (session('id')==$link->id_link && $errors->edit->has('form'))
                    <div class="alert alert-danger mb-0">{{ $errors->edit->first('form') }}</div>
                @endif
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="editLinkForm{{ $link->id_link }}" class="btn btn-danger">
                    <i class="bi bi-check-circle me-2"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
