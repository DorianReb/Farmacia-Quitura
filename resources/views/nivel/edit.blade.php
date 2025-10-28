{{-- MODAL EDITAR NIVEL - ID: editNivelModal{{ $nivel->id }} --}}
<div class="modal fade" id="editNivelModal{{ $nivel->id }}" tabindex="-1" aria-labelledby="editNivelModalLabel{{ $nivel->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editNivelModalLabel{{ $nivel->id }}">Editar Nivel: {{ $nivel->nombre }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('nivel.update', $nivel->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nivel_pasillo_id{{ $nivel->id }}" class="form-label">Pasillo</label>
                        <select name="pasillo_id" id="edit_nivel_pasillo_id{{ $nivel->id }}" class="form-select @error('pasillo_id', 'nivel_edit_'.$nivel->id) is-invalid @enderror" required>
                            <option value="">Seleccione un Pasillo</option>
                            @foreach ($pasillos as $pasillo)
                                <option value="{{ $pasillo->id }}" {{ old('pasillo_id', $nivel->pasillo_id) == $pasillo->id ? 'selected' : '' }}>
                                    {{ $pasillo->codigo }}
                                </option>
                            @endforeach
                        </select>
                        @error('pasillo_id', 'nivel_edit_'.$nivel->id)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_nivel_numero{{ $nivel->id }}" class="form-label">Número de Nivel</label>
                        <input type="number" 
                               name="numero" 
                               id="edit_nivel_numero{{ $nivel->id }}" 
                               class="form-control @error('numero', 'nivel_edit_'.$nivel->id) is-invalid @enderror" 
                               value="{{ old('numero', $nivel->numero) }}" 
                               required 
                               min="1" 
                               placeholder="Ej: 1 o 5">
                        @error('numero', 'nivel_edit_'.$nivel->id)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Nivel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->hasBag('nivel_edit_'.$nivel->id))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('editNivelModal{{ $nivel->id }}');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif