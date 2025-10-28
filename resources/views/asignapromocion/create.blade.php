<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('asignapromocion.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="{{ $id }}Label">Asignar Promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="promocion_id" class="form-label">Promoción</label>
                        <select name="promocion_id" id="promocion_id" class="form-select" required>
                            <option value="">Seleccione promoción</option>
                            @foreach($promociones as $promo)
                                <option value="{{ $promo->id }}">{{ $promo->porcentaje }}% - {{ $promo->autorizada_por }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="lote_id" class="form-label">Lote</label>
                        <select name="lote_id" id="lote_id" class="form-select" required>
                            <option value="">Seleccione lote</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->id }}">
                                    {{ $lote->codigo }} - {{ $lote->producto->nombre_comercial ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Asignar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>
