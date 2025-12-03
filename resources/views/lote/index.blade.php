@extends('layouts.sidebar-admin')

@section('content')
    <style>
        .card-soft {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(16,24,40,.06);
        }
        .section-title {
            font-weight: 800;
            letter-spacing: .04em;
        }
        .table-hover tbody tr:hover {
            background: #f7f9ff;
        }
        .btn-icon {
            padding: .45rem .6rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    @php
        use Carbon\Carbon;
        $rol = Auth::user()->rol ?? null;
        // Asegurar que exista la colección (por si acaso)
        $promosPorLote = $promosPorLote ?? collect();
    @endphp

    <div class="container-xxl">

        {{-- ENCABEZADO --}}
        <div class="row mb-2">
            <div class="col-12 text-center">
                <div class="bg-azul-marino text-white rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <h1 class="h4 m-0">Lotes</h1>
                </div>
            </div>
        </div>

        {{-- BOTÓN AGREGAR + BUSCADOR --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Botón Agregar (solo Admin y Superadmin) --}}
            @if(in_array($rol, ['Administrador','Superadmin']))
                <button type="button"
                        class="btn btn-success btn-icon shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#createModal"
                        title="Agregar nuevo lote">
                    <i class="fa-solid fa-plus"></i>
                </button>
            @endif

            {{-- Buscador --}}
            <form method="GET" action="{{ route('lote.index') }}" class="d-inline-block" style="min-width:300px;max-width:480px;width:100%;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por cualquier campo (código, producto, fechas, usuario, cantidad, precio)…">
                    @if(request('q'))
                        <a href="{{ route('lote.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button class="btn btn-success" title="Buscar">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- ALERTA DE ÉXITO --}}
        @if(session('success'))
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="alert alert-success shadow-sm mb-3">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- TABLA DE LOTES --}}
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-azul-marino text-white">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="width:120px;">Fecha caducidad</th>
                            <th style="width:120px;">Estado</th> {{-- 👈 NUEVA COLUMNA --}}
                            <th style="width:150px;">Promoción activa</th> {{-- 👈 NUEVA COLUMNA --}}
                            <th style="width:100px;">Cantidad</th>
                            <th>Precio compra</th>
                            <th>Fecha entrada</th>
                            <th style="width:200px;">Registrado por</th>
                            @if(in_array($rol, ['Administrador','Superadmin']))
                                <th class="text-end" style="width:220px;">Acciones</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($lotes as $lote)
                            @php
                                $fechaCad = $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad) : null;
                                $hoy = Carbon::today();

                                // Obtener promociones activas para este lote
                                $promosLote = $promosPorLote[$lote->id] ?? collect();
                                // Si hubiera más de una, tomamos la de mayor porcentaje (por si acaso)
                                $promoActiva = $promosLote->sortByDesc(function($a) {
                                    return $a->promocion->porcentaje ?? 0;
                                })->first();
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $lote->codigo }}</td>
                                <td>{{ $lote->producto->resumen ?? $lote->producto->nombre_comercial ?? '—' }}</td>

                                {{-- Fecha de caducidad --}}
                                <td>
                                    @if($fechaCad)
                                        {{ $fechaCad->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Estado (Vigente / Vencido) --}}
                                <td>
                                    @if($fechaCad)
                                        @if($fechaCad->isPast())
                                            <span class="badge bg-danger">Vencido</span>
                                        @else
                                            <span class="badge bg-success">Vigente</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Sin fecha</span>
                                    @endif
                                </td>

                                {{-- Promoción activa / historial --}}
                                <td>
                                    @php
                                        // Colección de asignaciones de promoción de este lote
                                        $promosLote = $promosPorLote[$lote->id] ?? collect();

                                        // Elegimos la promoción "más reciente" por fecha_inicio
                                        $asigRelevante = $promosLote->sortByDesc(function($a) {
                                            return $a->promocion->fecha_inicio ?? '0000-00-00';
                                        })->first();
                                    @endphp

                                    @if($asigRelevante && $asigRelevante->promocion)
                                        @php
                                            $p = $asigRelevante->promocion;
                                            $hoy = \Carbon\Carbon::today()->toDateString();
                                            $vigente = ($p->fecha_inicio <= $hoy && $p->fecha_fin >= $hoy);
                                        @endphp

                                        <span class="badge bg-warning text-dark me-1">
            {{ number_format($p->porcentaje, 2) }}%
        </span>

                                        @if($vigente)
                                            <span class="badge bg-success">Vigente</span>
                                        @else
                                            <span class="badge bg-secondary">No vigente</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin promoción</span>
                                    @endif
                                </td>

                                <td>{{ $lote->cantidad ?? '0' }}</td>
                                <td>${{ number_format($lote->precio_compra ?? 0, 2) }}</td>
                                <td>{{ $lote->fecha_entrada ?? '—' }}</td>
                                <td>{{ $lote->usuario->nombre_completo ?? '—' }}</td>

                                {{-- Acciones solo visibles a Admin/Superadmin --}}
                                @if(in_array($rol, ['Administrador','Superadmin']))
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Editar --}}
                                            <button class="btn btn-warning shadow-sm rounded-pill btn-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $lote->id }}"
                                                    title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('lote.destroy', $lote) }}"
                                                  method="POST"
                                                  class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger shadow-sm rounded-pill btn-icon ms-1"
                                                        type="submit"
                                                        title="Eliminar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ in_array($rol, ['Administrador','Superadmin']) ? 10 : 9 }}" class="text-center py-4 text-muted">
                                    No hay lotes registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted order-2 order-md-1 text-center text-md-start">
                    Mostrando {{ $lotes->firstItem() ?? 0 }}–{{ $lotes->lastItem() ?? 0 }} de {{ $lotes->total() }}
                    @if(request('q')) • Filtro: "{{ request('q') }}" @endif
                </small>

                @if($lotes->hasPages())
                    <nav aria-label="Paginación de lotes" class="order-1 order-md-2">
                        {{ $lotes->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                @endif
            </div>
        </div>
    </div>



    {{-- Modal de creación: solo Admin/Superadmin --}}
    @if(in_array($rol, ['Administrador','Superadmin']))
        @include('lote.create')
    @endif

    {{-- Modales de edición: solo Admin/Superadmin --}}
    @if(in_array($rol, ['Administrador','Superadmin']))
        @foreach($lotes as $lote)
            @include('lote.edit', ['lote' => $lote])
        @endforeach
    @endif

    @php
        $fromModal = old('from_modal') ?? session('from_modal');
    @endphp

    @if ($errors->any() && $fromModal === 'edit_lote' && session('edit_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('editModal{{ session('edit_id') }}');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endif

    @if ($errors->any() && $fromModal === 'create_lote')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('createModal');
                if (el) {
                    const modal = new bootstrap.Modal(el);
                    modal.show();
                }
            });
        </script>
    @endif

    @if ($errors->has('procedimiento') && $fromModal === 'create_lote')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const el = document.getElementById('createModal');
                    if (el) {
                        const modal = new bootstrap.Modal(el);
                        modal.show();
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al registrar lote',
                            text: @json($errors->first('procedimiento')),
                        });
                    }
                });
            </script>
        @endpush
    @endif


@endsection
