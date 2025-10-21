@extends('layouts.sidebar-admin') {{-- tu layout con sidebar --}}

@section('content')
    <div class="container-xxl">

        {{-- Encabezado + acciones rápidas --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h2 class="mb-1">Catálogos</h2>
                <div class="text-muted small">Administra todos los catálogos en una sola vista.</div>
            </div>
            <div class="d-flex gap-2">
                {{-- Menú "Nuevo…" para alta rápida --}}
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">Nuevo…</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item open-remote" data-title="Nueva marca"             data-url="{{ route('marcas.create') }}">Marca</a></li>
                        <li><a class="dropdown-item open-remote" data-title="Nueva forma"             data-url="{{ route('formas.create') }}">Forma farmacéutica</a></li>
                        <li><a class="dropdown-item open-remote" data-title="Nueva presentación"      data-url="{{ route('presentaciones.create') }}">Presentación</a></li>
                        <li><a class="dropdown-item open-remote" data-title="Nueva unidad de medida"  data-url="{{ route('unidades.create') }}">Unidad de medida</a></li>
                        <li><a class="dropdown-item open-remote" data-title="Nueva categoría"         data-url="{{ route('categorias.create') }}">Categoría</a></li>
                        <li><a class="dropdown-item open-remote" data-title="Nuevo nombre científico" data-url="{{ route('nombres.create') }}">Nombre científico</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Sub-nav sticky (anclas) --}}
        <div class="position-sticky bg-white py-2 mb-3" style="top:0; z-index:2; border-bottom:1px solid #eee">
            <div class="d-flex flex-wrap gap-2">
                @foreach($sections as $s)
                    <a href="#sec-{{ $s['key'] }}" class="btn btn-outline-primary btn-sm">
                        {{ $s['label'] }} <span class="badge bg-secondary">{{ $s['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- SECCIONES APILADAS (una tarjeta por catálogo) --}}
        @foreach($sections as $s)
            <section id="sec-{{ $s['key'] }}" class="card mb-4 shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center gap-2 justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0">{{ $s['label'] }}</h5>
                        <span class="text-muted small">({{ $s['count'] }} registros)</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        {{-- Buscar solo en esta sección --}}
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input
                                type="search"
                                class="form-control sec-search"
                                placeholder="Buscar en {{ $s['label'] }}"
                                data-section="{{ $s['key'] }}">
                        </div>

                        {{-- Toggle "Ver eliminados" de esta sección --}}
                        <div class="form-check form-switch">
                            <input class="form-check-input sec-deleted" type="checkbox" id="chk-{{ $s['key'] }}" data-section="{{ $s['key'] }}">
                            <label class="form-check-label small" for="chk-{{ $s['key'] }}">Ver eliminados</label>
                        </div>

                        {{-- Agregar directo en esta sección (abre modal con tu form) --}}
                        @php
                            $createRoutes = [
                              'marcas'             => 'marcas.create',
                              'formas'             => 'formas.create',
                              'presentaciones'     => 'presentaciones.create',
                              'unidades'           => 'unidades.create',
                              'categorias'         => 'categorias.create',
                              'nombres-cientificos'=> 'nombres.create',
                            ];
                        @endphp
                        <button
                            class="btn btn-success btn-sm open-remote"
                            data-title="Agregar — {{ $s['label'] }}"
                            data-url="{{ route($createRoutes[$s['key']]) }}">
                            Agregar {{ Str::of($s['label'])->singular() }}
                        </button>
                    </div>
                </div>

                {{-- Contenedor de la TABLA (se carga por AJAX desde tus vistas parciales) --}}
                <div
                    class="card-body p-0 sec-container"
                    data-section="{{ $s['key'] }}"
                    data-url="{{ route('catalogos.section', $s['key']) }}"
                    data-loaded="0">
                    {{-- Estado de carga inicial --}}
                    <div class="p-4 text-center text-muted small">Cargando {{ $s['label'] }}…</div>
                </div>
            </section>
        @endforeach
    </div>

    {{-- Modal reutilizable para crear/editar (carga remota de tus formularios) --}}
    <div class="modal fade" id="modal-remote" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 text-center text-muted small">Cargando…</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Utilidad: carga una sección con sus parámetros (q, deleted, page)
        async function loadSection(container, params = {}) {
            const url = new URL(container.dataset.url, window.location.origin);
            Object.entries(params).forEach(([k,v]) => v!=null && url.searchParams.set(k, v));
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            container.innerHTML = await res.text();

            // Interceptar paginación dentro de la sección (para no recargar toda la página)
            container.querySelectorAll('.pagination a').forEach(a => {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    const pageUrl = new URL(a.href);
                    params.page = pageUrl.searchParams.get('page') || 1;
                    loadSection(container, params);
                });
            });
        }

        // Carga diferida: solo cuando la sección aparece en pantalla
        const io = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    if (el.dataset.loaded === '0') {
                        el.dataset.loaded = '1';
                        loadSection(el);
                    }
                }
            });
        }, { rootMargin: '200px' });

        document.querySelectorAll('.sec-container').forEach(el => io.observe(el));

        // Búsqueda local por sección
        document.querySelectorAll('.sec-search').forEach(input => {
            let timer;
            input.addEventListener('input', e => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const section = e.target.dataset.section;
                    const container = document.querySelector(`.sec-container[data-section="${section}"]`);
                    const deleted = document.querySelector(`.sec-deleted[data-section="${section}"]`).checked ? 1 : 0;
                    loadSection(container, { q: e.target.value, deleted });
                }, 300);
            });
        });

        // Toggle "Ver eliminados" por sección
        document.querySelectorAll('.sec-deleted').forEach(chk => {
            chk.addEventListener('change', e => {
                const section = e.target.dataset.section;
                const container = document.querySelector(`.sec-container[data-section="${section}"]`);
                const q = document.querySelector(`.sec-search[data-section="${section}"]`).value || '';
                loadSection(container, { q, deleted: e.target.checked ? 1 : 0 });
            });
        });

        // Modal remoto reutilizable (abre tus formularios create/edit SIN salir de la página)
        const modalEl = document.getElementById('modal-remote');
        const modal = new bootstrap.Modal(modalEl);

        document.body.addEventListener('click', async (e) => {
            const btn = e.target.closest('.open-remote');
            if (!btn) return;

            e.preventDefault();
            modalEl.querySelector('.modal-title').textContent = btn.dataset.title || 'Formulario';
            modalEl.querySelector('.modal-body').innerHTML = '<div class="p-4 text-center text-muted small">Cargando…</div>';
            modal.show();

            const res = await fetch(btn.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            modalEl.querySelector('.modal-body').innerHTML = await res.text();
        });

        // Si tus formularios, al guardar, emiten un evento window.postMessage({type:'catalogo:saved', section:'marcas'})
        window.addEventListener('message', (e) => {
            if (e.data?.type === 'catalogo:saved') {
                // Recarga la sección afectada
                const section = e.data.section;
                const container = document.querySelector(`.sec-container[data-section="${section}"]`);
                if (container) loadSection(container);
                modal.hide();
            }
        });
    </script>
@endpush
