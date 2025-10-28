{{-- MODAL EDITAR PASILLO - ID: editPasilloModal{{ $pasillo->id }} --}}
<div class="modal fade" id="editPasilloModal{{ $pasillo->id }}" tabindex="-1" aria-labelledby="editPasilloModalLabel{{ $pasillo->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPasilloModalLabel{{ $pasillo->id }}">Editar Pasillo: {{ $pasillo->codigo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pasillo.update', $pasillo->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_pasillo_codigo{{ $pasillo->id }}" class="form-label">Código del Pasillo</label>
                        <input type="text" 
                               name="codigo" 
                               id="edit_pasillo_codigo{{ $pasillo->id }}" 
                               class="form-control @error('codigo', 'pasillo_edit_'.$pasillo->id) is-invalid @enderror" 
                               value="{{ old('codigo', $pasillo->codigo) }}" 
                               required 
                               maxlength="10"
                               placeholder="Ej: P01">
                        @error('codigo', 'pasillo_edit_'.$pasillo->id)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Pasillo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->hasBag('pasillo_edit_'.$pasillo->id))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('editPasilloModal{{ $pasillo->id }}');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif