<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\MarcaController;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


    // Vista unificada
    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/', [CatalogosController::class, 'index'])->name('index');

        // Secciones (cargan el HTML de tus parciales)
        Route::get('/section/{section}', [CatalogosController::class, 'section'])
            ->whereIn('section', [
                'marcas','formas','presentaciones','unidades','categorias','nombres-cientificos'
            ])->name('section');
    });

    // Formularios create/edit/destroy de cada catálogo (viven en archivos aparte)
    Route::resource('categoria',\App\Http\Controllers\CategoriaController::class);
    Route::resource('marca',\App\Http\Controllers\MarcaController::class);
    Route::resource('presentacion',\App\Http\Controllers\PresentacionController::class);
    Route::resource('formaFarmaceutica',\App\Http\Controllers\FormaFarmaceuticaController::class);
    Route::resource('unidad_medida',\App\Http\Controllers\UnidadMedidaController::class);
    Route::resource('nombreCientifico',\App\Http\Controllers\NombreCientificoController::class);

    //SofDeletes
// Presentaciones
Route::get   ('/presentaciones/eliminados', [PresentacionController::class,'eliminados'])->name('presentacion.eliminados');
Route::patch ('/presentaciones/{id}/restaurar', [PresentacionController::class,'restaurar'])->name('presentacion.restaurar');
Route::delete('/presentaciones/{id}/forzar-eliminacion', [PresentacionController::class,'forzarEliminacion'])->name('presentacion.forzar-eliminacion');

// Soft deletes de Marcas
Route::prefix('marcas')->name('marca.')->group(function () {
    Route::get   ('eliminados',              [MarcaController::class, 'eliminados'])->name('eliminados');
    Route::patch ('{id}/restaurar',          [MarcaController::class, 'restaurar'])->name('restaurar');
    Route::delete('{id}/forzar-eliminacion', [MarcaController::class, 'forzarEliminacion'])->name('forzar-eliminacion');
});

