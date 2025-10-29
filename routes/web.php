<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\AsignaUbicacionController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AsignaPromocionController;
use App\Http\Controllers\AsignaComponenteController;
use App\Http\Controllers\DetalleVentaController;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

/*
|--------------------------------------------------------------------------
| ESTADOS / HOME (públicas)
|--------------------------------------------------------------------------
*/
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::view('/cuenta/pendiente', 'estado.pendiente')->name('estado.pendiente');
Route::view('/cuenta/rechazada', 'estado.rechazado')->name('estado.rechazado');

/*
|--------------------------------------------------------------------------
| SUPERADMIN (solo Superadmin)  --> mantiene nombres: superadmin.usuarios.*, superadmin.solicitudes.*
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'estado', 'role:Superadmin'])
    ->prefix('superadmin')->name('superadmin.')->group(function () {

        // Usuarios
        Route::resource('usuarios', UsuarioController::class);

        // Solicitudes
        Route::get('solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::patch('solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::patch('solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');
        Route::delete('solicitudes/{id}', [SolicitudController::class, 'destroy'])->name('solicitudes.destroy');
    });

/*
|--------------------------------------------------------------------------
| INVENTARIO (Vendedor, Administrador, Superadmin)
| - Vendedor: SOLO consulta (index) en catálogos y lotes (sin create/store/edit/update/delete/show)
| - Admin/Superadmin: tendrán sus rutas completas en el bloque de ADMINISTRACIÓN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'estado', 'role:Vendedor,Administrador,Superadmin'])->group(function () {

    // Lotes: SOLO índice (para Vendedor también)
    Route::resource('lote', LoteController::class)->only(['index']);

    // Solo índices de catálogos / vistas de referencia
    Route::resource('producto', ProductoController::class)->only(['index']);
    Route::resource('asigna_componentes', AsignaComponenteController::class)->only(['index']);
    Route::resource('ubicacion', AsignaUbicacionController::class)->only(['index']);
    Route::resource('pasillo', \App\Http\Controllers\PasilloController::class)->only(['index']);
    Route::resource('nivel',   \App\Http\Controllers\NivelController::class)->only(['index']);
});

/*
|--------------------------------------------------------------------------
| ADMINISTRACIÓN (Administrador y Superadmin)
| - Aquí van TODAS las acciones de modificación de inventario y catálogos
| - Incluye: creación/edición de Lotes y asignación de Promociones
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'estado', 'role:Administrador,Superadmin'])->group(function () {

    // Catálogos completos
    Route::resource('categoria', \App\Http\Controllers\CategoriaController::class);
    Route::resource('marca', \App\Http\Controllers\MarcaController::class);
    Route::resource('presentacion', \App\Http\Controllers\PresentacionController::class);
    Route::resource('formaFarmaceutica', \App\Http\Controllers\FormaFarmaceuticaController::class);
    Route::resource('unidad_medida', \App\Http\Controllers\UnidadMedidaController::class);
    Route::resource('nombreCientifico', \App\Http\Controllers\NombreCientificoController::class);

    // SoftDeletes Presentaciones
    Route::get('/presentaciones/eliminados', [PresentacionController::class, 'eliminados'])->name('presentacion.eliminados');
    Route::patch('/presentaciones/{id}/restaurar', [PresentacionController::class, 'restaurar'])->name('presentacion.restaurar');
    Route::delete('/presentaciones/{id}/forzar-eliminacion', [PresentacionController::class, 'forzarEliminacion'])->name('presentacion.forzar-eliminacion');

    // SoftDeletes Marcas
    Route::prefix('marcas')->name('marca.')->group(function () {
        Route::get('eliminados', [MarcaController::class, 'eliminados'])->name('eliminados');
        Route::patch('{id}/restaurar', [MarcaController::class, 'restaurar'])->name('restaurar');
        Route::delete('{id}/forzar-eliminacion', [MarcaController::class, 'forzarEliminacion'])->name('forzar-eliminacion');
    });

    // Producto: todo excepto el index (ya está en el bloque compartido)
    Route::resource('producto', ProductoController::class)->except(['index']);

    // Lote: aquí sí se permite crear/editar/eliminar/mostrar (solo Gerente/Superadmin)
    Route::resource('lote', LoteController::class)->only(['create','store','edit','update','destroy','show']);

    // Ubicaciones / componentes / pasillos / niveles: gestión completa
    Route::resource('ubicacion', AsignaUbicacionController::class)->except(['index']);
    Route::resource('asigna_componentes', AsignaComponenteController::class)->except(['index']);
    Route::resource('pasillo', \App\Http\Controllers\PasilloController::class)->except(['index']);
    Route::resource('nivel', \App\Http\Controllers\NivelController::class)->except(['index']);

    // Promociones y asignación de promociones (solo Gerente/Superadmin)
    Route::resource('promocion', PromocionController::class);
    Route::resource('asignapromocion', AsignaPromocionController::class);
});

/*
|--------------------------------------------------------------------------
| VENTAS (Vendedor, Admin y Superadmin)
| - Acepta efectivo, no devoluciones (reglas de negocio en controlador)
| - ¡OJO! Se retiró asignapromocion y CRUD de pasillos de este bloque
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'estado', 'role:Vendedor,Administrador,Superadmin'])->group(function () {
    Route::get('/venta', [VentaController::class, 'index'])->name('venta.index');
    Route::get('venta/producto/{codigo}', [VentaController::class, 'buscarProductoPorCodigo'])->name('venta.buscar.api');
    Route::post('/venta/store', [VentaController::class, 'store'])->name('venta.store');
    Route::get('/venta/historial', [VentaController::class, 'historial'])->name('venta.historial');
    Route::get('/productos/menu', [ProductoController::class, 'menu'])->name('producto.menu');
    Route::get('/detalleventa', [DetalleVentaController::class, 'index'])->name('detalleventa.index');
    Route::get('/venta/{venta}', [VentaController::class, 'detalles'])->name('venta.detalles');
    Route::delete('/venta/anular/{venta}', [VentaController::class, 'anular'])->name('venta.anular');
    Route::get('/venta/ticket/{venta}', [VentaController::class, 'ticket'])->name('venta.ticket');

    // Dashboard vendedor
    Route::view('/dashboard/vendedor', 'vendedor.dashboard')->name('vendedor.dashboard');
});
