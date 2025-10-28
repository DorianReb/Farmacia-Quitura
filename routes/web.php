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

        // Usuarios (resource mantiene los mismos nombres superadmin.usuarios.*)
        Route::resource('usuarios', UsuarioController::class);

        // Solicitudes (mismos nombres)
        Route::get('solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::patch('solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::patch('solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');
        Route::delete('solicitudes/{id}', [SolicitudController::class, 'destroy'])->name('solicitudes.destroy');
    });

/*
|--------------------------------------------------------------------------
| ADMINISTRACIÓN (Admin y Superadmin)  --> mantiene TODOS los nombres originales
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'estado', 'role:Administrador,Superadmin'])->group(function () {

    // Formularios create/edit/destroy de cada catálogo (nombres iguales)
    Route::resource('categoria', \App\Http\Controllers\CategoriaController::class);
    Route::resource('marca', \App\Http\Controllers\MarcaController::class);
    Route::resource('presentacion', \App\Http\Controllers\PresentacionController::class);
    Route::resource('formaFarmaceutica', \App\Http\Controllers\FormaFarmaceuticaController::class);
    Route::resource('unidad_medida', \App\Http\Controllers\UnidadMedidaController::class);
    Route::resource('nombreCientifico', \App\Http\Controllers\NombreCientificoController::class);

    // SoftDeletes Presentaciones (mismos nombres)
    Route::get('/presentaciones/eliminados', [PresentacionController::class, 'eliminados'])->name('presentacion.eliminados');
    Route::patch('/presentaciones/{id}/restaurar', [PresentacionController::class, 'restaurar'])->name('presentacion.restaurar');
    Route::delete('/presentaciones/{id}/forzar-eliminacion', [PresentacionController::class, 'forzarEliminacion'])->name('presentacion.forzar-eliminacion');

    // SoftDeletes Marcas (mismos nombres y prefix 'marcas' ya existente)
    Route::prefix('marcas')->name('marca.')->group(function () {
        Route::get('eliminados', [MarcaController::class, 'eliminados'])->name('eliminados');
        Route::patch('{id}/restaurar', [MarcaController::class, 'restaurar'])->name('restaurar');
        Route::delete('{id}/forzar-eliminacion', [MarcaController::class, 'forzarEliminacion'])->name('forzar-eliminacion');
    });

    // Inventario / Gestión (mismos nombres)
    Route::resource('producto', ProductoController::class);
    Route::resource('lote', App\Http\Controllers\LoteController::class);
    Route::resource('ubicacion', AsignaUbicacionController::class);
    Route::resource('promocion', PromocionController::class);
    Route::resource('asignapromocion', AsignaPromocionController::class);
    Route::resource('asigna_componentes', AsignaComponenteController::class);
    Route::resource('pasillo', App\Http\Controllers\PasilloController::class)->except(['index', 'show', 'create', 'edit']);
    Route::resource('nivel', App\Http\Controllers\NivelController::class)->except(['index', 'show', 'create', 'edit']);
});

/*
|--------------------------------------------------------------------------
| VENTAS (Vendedor, Admin y Superadmin)  --> mantiene nombres: venta.index / venta.store
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'estado', 'role:Vendedor,Administrador,Superadmin'])->group(function () {
    Route::get('/venta', [VentaController::class, 'index'])->name('venta.index');
    Route::get('venta/producto/{codigo}', [VentaController::class, 'buscarProductoPorCodigo'])->name('venta.buscar.api');
    Route::post('/venta/store', [VentaController::class, 'store'])->name('venta.store');
    Route::get('/venta/historial', [VentaController::class, 'historial'])->name('venta.historial');
    Route::resource('pasillos', PasilloController::class)->names('pasillo');
    Route::resource('asignapromocion', AsignaPromocionController::class)->names('asignapromocion')->only([
    'index', 'create', 'store', 'destroy']);
    Route::get('/productos/menu', [ProductoController::class, 'menu'])->name('producto.menu');
    Route::get('/detalleventa', [DetalleVentaController::class, 'index'])->name('detalleventa.index');
    Route::get('/venta/{venta}', [VentaController::class, 'detalles'])->name('venta.detalles');
    Route::delete('/venta/anular/{venta}', [VentaController::class, 'anular'])->name('venta.anular');
    Route::get('/venta/ticket/{venta}', [VentaController::class, 'ticket'])->name('venta.ticket');

    // Ruta temporal de dashboard vendedor (nombre intacto)
    Route::view('/dashboard/vendedor', 'vendedor.dashboard')->name('vendedor.dashboard');
});
