@extends('layouts.app')

@section('title', 'Links')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-3">Semua Link</h1>
        <p class="text-muted">Kelola link shortener publik</p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#createLinkModal">
            <i class="bi bi-plus-circle me-2"></i>
            Buat Link Baru
        </button>
    </div>
</div>

<!-- Search and Filter -->
<form method="GET" action="{{ route('links') }}" id="filterForm" class="row mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input
                type="text"
                class="form-control border-start-0"
                name="search"
                id="searchInput"
                placeholder="Search by name, alias, or URL..."
                value="{{ request('search') }}">
            @if(request('search'))
                <a href="{{ route('links') }}" class="btn btn-outline-secondary" title="Clear search">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" name="status" id="statusFilter">
            <option value="all" @if(!request('status') || request('status') === 'all') selected @endif>Semua Link</option>
            <option value="active" @if(request('status') === 'active') selected @endif>Aktif</option>
            <option value="inactive" @if(request('status') === 'inactive') selected @endif>Tidak Aktif</option>
        </select>
    </div>
</form>

{{-- Success Alert --}}
@if(session('success'))
    <x-alert-success :message="session('success')" />
@endif

{{-- Error Alert --}}
@if($errors->has('delete'))
    <x-alert-error :message="$errors->first('delete')" />
@endif

@if($errors->has('form'))
    <x-alert-error :message="$errors->first('form')" />
@endif

<!-- Links List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 ps-4">Short Link</th>
                                <th class="border-0">Nama</th>
                                <th class="border-0">URL Asli</th>
                                <th class="border-0">Dibuat</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($links as $link)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ url($link->new_link) }}" class="text-decoration-none fw-semibold" target="_blank">{{ url($link->new_link) }}</a>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $link->name }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small" title="{{ $link->true_link }}">{{ Str::limit($link->true_link, 50) }}</span>
                                </td>
                                <td><small class="text-muted">{{ $link->created_at ? $link->created_at->format('d M Y') : '-' }}</small></td>
                                <td>
                                    @if($link->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary m-1" title="Edit" data-bs-toggle="modal" data-bs-target="#editLinkModal{{ $link->id_link }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if(Route::has('links.analytics'))
                                    <a href="{{ route('links.analytics', $link->id_link) }}" class="btn btn-sm btn-outline-primary m-1" title="Analytics">
                                        <i class="bi bi-bar-chart"></i>
                                    </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger m-1" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteLinkModal" data-link-id="{{ $link->id_link }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-link-45deg fs-1 d-block mb-2"></i>
                                    Belum ada link. Buat link pertama Anda!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Link Modal -->
<x-create-link-modal />

<!-- Edit Link Modal -->
@foreach ($links as $link)
    <x-edit-link-modal :link="$link" />
@endforeach

<x-delete-account-modal
    :modalId="'deleteLinkModal'"
    :title="'Hapus Link Secara Permanen'"
    :message="'Tindakan ini tidak dapat dibatalkan. Link akan dihapus secara permanen.'"
    :formId="'deleteLinkForm'"
    :formAction="'#'"
    :buttonId="'deleteLinkBtn'"
    :buttonText="'Hapus Link Selamanya'"
    :checkboxId="'confirmDeleteLink'"
    :confirmText="'Saya mengerti dan ingin menghapus link ini secara permanen'"
    :requireConfirm="true"
    :method="'DELETE'"
/>

@push('scripts')
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite('resources/js/links.js')
@else
    <script src="{{ asset('js/links.js') }}"></script>
@endif
<script>
    // Auto-open modal if there are errors
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->hasBag('new') || isset($newopened))
            var createModalEl = document.getElementById('createLinkModal');
            if (createModalEl) {
                var createModal = new bootstrap.Modal(createModalEl);
                createModal.show();
            }
        @endif

        @if($errors->hasBag('edit') && session('id'))
            var editModalId = 'editLinkModal{{ session("id") }}';
            var editModalElement = document.getElementById(editModalId);
            if (editModalElement) {
                var editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
            }
        @endif

        // Real-time filter on status change
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');

        // Submit form on filter change
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                filterForm.submit();
            });
        }

        // Real-time search with debounce
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 500);
            });
        }

        // Handle delete link modal
        const deleteLinkModal = document.getElementById('deleteLinkModal');
        const deleteLinkForm = document.getElementById('deleteLinkForm');
        const deleteButtons = document.querySelectorAll('[data-bs-target="#deleteLinkModal"]');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const linkId = this.dataset.linkId;
                const formAction = "{{ route('links.destroy', ':ID') }}".replace(':ID', linkId);
                deleteLinkForm.action = formAction;
            });
        });

        // Reset delete link modal when closed
        if (deleteLinkModal) {
            deleteLinkModal.addEventListener('hidden.bs.modal', function() {
                const checkbox = document.getElementById('confirmDeleteLink');
                const btn = document.getElementById('deleteLinkBtn');
                if (checkbox) checkbox.checked = false;
                if (btn) btn.disabled = true;
            });
        }
    });
</script>
@endpush
@endsection
