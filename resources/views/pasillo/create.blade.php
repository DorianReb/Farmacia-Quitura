{{-- MODAL CREAR PASILLO --}}
<div class="modal fade" id="createPasilloModal" tabindex="-1" aria-labelledby="createPasilloModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createPasilloModalLabel">Crear Nuevo Pasillo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pasillo.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pasillo_codigo" class="form-label">Código del Pasillo</label>
                        {{-- El campo `codigo` es el único `fillable` en tu modelo Pasillo --}}
                        <input type="text" 
                               name="codigo" 
                               id="pasillo_codigo" 
                               class="form-control @error('codigo', 'pasillo_create') is-invalid @enderror" 
                               value="{{ old('codigo') }}" 
                               required 
                               maxlength="10"
                               placeholder="Ej: P01">
                        @error('codigo', 'pasillo_create')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Guardar Pasillo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->hasBag('pasillo_create'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('createPasilloModal');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif