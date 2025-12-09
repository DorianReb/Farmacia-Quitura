@php
    // Acepta $row (cuando se incluye como parcial) o $asignacion (cuando vienes del controlador edit)
    $row = $row ?? ($asignacion ?? null);

    // Guardas seguros (null-safe). Evita acceder a propiedades si $row es null.
    $valFuerzaCant = old('fuerza_cantidad', $row?->fuerza_cantidad);
    $valBaseCant   = old('base_cantidad',   $row?->base_cantidad);
    $isThisModal   = session('from_modal') === 'edit_asigna_componente'
                  && (int)session('edit_id') === (int)($row->id ?? 0);
@endphp

@if(!$row)
    <div class="alert alert-warning m-2">
        No se recibió la asignación a editar.
    </div>
@else
<div class="modal fade" id="editAsignaComponenteModal{{ $row->id }}" tabindex="-1"
     aria-labelledby="editAsignaComponenteLabel{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editAsignaComponenteLabel{{ $row->id }}">
                    <i class="fa-solid fa-diagram-project me-1"></i> Editar asignación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && $isThisModal)
                    <div class="alert alert-danger">
                        <strong>Revisa los campos:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('asigna_componentes.update', $row->id) }}" method="POST" autocomplete="off" class="row g-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_modal" value="edit_asigna_componente">
                    <input type="hidden" name="edit_id" value="{{ $row->id }}">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">

                    {{-- Producto (nombre comercial) --}}
                    <div class="col-12 col-md-6">
                        <label for="producto_id_edit_{{ $row->id }}" class="form-label">Producto (nombre comercial) <span class="text-danger">*</span></label>
                        <select name="producto_id" id="producto_id_edit_{{ $row->id }}"
                                class="form-select @if($isThisModal) @error('producto_id') is-invalid @enderror @endif" required>
                            @foreach($productos as $p)
                                <option value="{{ $p->id }}"
                                    {{ (old('producto_id', $row->producto_id) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                        @if($isThisModal) @error('producto_id') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>

                    {{-- Componente (nombre científico) --}}
                    <div class="col-12 col-md-6">
                        <label for="nombre_cientifico_id_edit_{{ $row->id }}" class="form-label">Componente (nombre científico) <span class="text-danger">*</span></label>
                        <select name="nombre_cientifico_id" id="nombre_cientifico_id_edit_{{ $row->id }}"
                                class="form-select @if($isThisModal) @error('nombre_cientifico_id') is-invalid @enderror @endif" required>
                            @foreach($componentes as $c)
                                <option value="{{ $c->id }}"
                                    {{ (old('nombre_cientifico_id', $row->nombre_cientifico_id) == $c->id) ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if($isThisModal) @error('nombre_cientifico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>

                    {{-- Fuerza --}}
                    <div class="col-6 col-md-3">
                        <label for="fuerza_cantidad_edit_{{ $row->id }}" class="form-label">Fuerza (cantidad) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0"
                               class="form-control @if($isThisModal) @error('fuerza_cantidad') is-invalid @enderror @endif"
                               id="fuerza_cantidad_edit_{{ $row->id }}" name="fuerza_cantidad"
                               value="{{ $valFuerzaCant }}" required>
                        @if($isThisModal) @error('fuerza_cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fuerza_unidad_id_edit_{{ $row->id }}" class="form-label">Fuerza (unidad) <span class="text-danger">*</span></label>
                        <select name="fuerza_unidad_id" id="fuerza_unidad_id_edit_{{ $row->id }}"
                                class="form-select @if($isThisModal) @error('fuerza_unidad_id') is-invalid @enderror @endif" required>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}"
                                    {{ (old('fuerza_unidad_id', $row->fuerza_unidad_id) == $u->id) ? 'selected' : '' }}>
                                    {{ $u->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if($isThisModal) @error('fuerza_unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>

                    {{-- Base --}}
                    <div class="col-6 col-md-3">
                        <label for="base_cantidad_edit_{{ $row->id }}" class="form-label">Base (cantidad) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0"
                               class="form-control @if($isThisModal) @error('base_cantidad') is-invalid @enderror @endif"
                               id="base_cantidad_edit_{{ $row->id }}" name="base_cantidad"
                               value="{{ $valBaseCant }}" required>
                        @if($isThisModal) @error('base_cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="base_unidad_id_edit_{{ $row->id }}" class="form-label">Base (unidad) <span class="text-danger">*</span></label>
                        <select name="base_unidad_id" id="base_unidad_id_edit_{{ $row->id }}"
                                class="form-select @if($isThisModal) @error('base_unidad_id') is-invalid @enderror @endif" required>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}"
                                    {{ (old('base_unidad_id', $row->base_unidad_id) == $u->id) ? 'selected' : '' }}>
                                    {{ $u->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if($isThisModal) @error('base_unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-check"></i> Actualizar
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endif

