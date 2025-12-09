{{-- modal_asigna_edit.blade.php --}}
<div class="modal fade" id="editAsignaModal{{ $asigna->id }}" tabindex="-1"
     aria-labelledby="editAsignaModal{{ $asigna->id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('asignapromocion.update', $asigna->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editAsignaModal{{ $asigna->id }}Label">Editar Asignación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    {{-- PROMOCIÓN --}}
                    <div class="mb-3">
                        <label for="promocion_id_{{ $asigna->id }}" class="form-label">Promoción</label>
                        <select name="promocion_id"
                                id="promocion_id_{{ $asigna->id }}"
                                class="form-select"
                                required>
                            <option value="">Seleccione promoción</option>
                            @foreach($promociones as $promo)
                                @php
                                    $inicio = \Carbon\Carbon::parse($promo->fecha_inicio)->format('d/m/Y');
                                    $fin    = \Carbon\Carbon::parse($promo->fecha_fin)->format('d/m/Y');
                                @endphp
                                <option value="{{ $promo->id }}"
                                        @if($asigna->promocion_id == $promo->id) selected @endif>
                                    {{ number_format($promo->porcentaje, 2) }}% — {{ $inicio }} a {{ $fin }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- LOTE --}}
                    <div class="mb-3">
                        <label for="lote_id_{{ $asigna->id }}" class="form-label">Lote</label>
                        <select name="lote_id"
                                id="lote_id_{{ $asigna->id }}"
                                class="form-select"
                                required>
                            <option value="">Seleccione lote</option>
                            @foreach($lotes as $lote)
                                @php
                                    $p = $lote->producto;
                                    $nombre    = trim($p->nombre_comercial ?? '');
                                    $contenido = trim($p->contenido ?? '');
                                    $forma     = trim($p->formaFarmaceutica->nombre ?? '');

                                    $partesCortas = array_filter([$nombre, $contenido ?: null, $forma ?: null]);
                                    $etiquetaProducto = implode(' ', $partesCortas);
                                @endphp

                                <option value="{{ $lote->id }}">
                                    {{ $lote->codigo }} - {{ $etiquetaProducto }}
                                    (vence: {{ \Carbon\Carbon::parse($lote->fecha_caducidad)->format('d/m/Y') }})
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>
