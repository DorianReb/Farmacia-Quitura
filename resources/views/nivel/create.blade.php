{{-- MODAL CREAR NIVEL --}}
<div class="modal fade" id="createNivelModal" tabindex="-1" aria-labelledby="createNivelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createNivelModalLabel">Crear Nuevo Nivel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('nivel.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nivel_pasillo_id" class="form-label">Pasillo</label>
                        <select name="pasillo_id" id="nivel_pasillo_id" class="form-select @error('pasillo_id', 'nivel_create') is-invalid @enderror" required>
                            <option value="">Seleccione un Pasillo</option>
                            @foreach ($pasillos as $pasillo)
                                <option value="{{ $pasillo->id }}" {{ old('pasillo_id') == $pasillo->id ? 'selected' : '' }}>
                                    {{ $pasillo->codigo }}
                                </option>
                            @endforeach
                        </select>
                        @error('pasillo_id', 'nivel_create')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nivel_numero" class="form-label">Número de Nivel</label>
                        {{-- El campo `numero` es el otro `fillable` en tu modelo Nivel --}}
                        <input type="number" 
                               name="numero" 
                               id="nivel_numero" 
                               class="form-control @error('numero', 'nivel_create') is-invalid @enderror" 
                               value="{{ old('numero') }}" 
                               required 
                               min="1" 
                               placeholder="Ej: 1 o 5">
                        @error('numero', 'nivel_create')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Guardar Nivel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->hasBag('nivel_create'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('createNivelModal');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif