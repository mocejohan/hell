<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DictamenController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DictamenPdfController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('dashboard');
    return view('mesadecontrol');
})->middleware('auth');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        // return view('dashboard');
        return view('mesadecontrol');
    })->name('dashboard');

    Route::get('/mesadecontrol', function () {
        return view('mesadecontrol');
    })->name('mesadecontrol');

    Route::get('/estadisticas', function () {
        return view('estadisticas');
    })->name('estadisticas');
    Route::get('/consultas', function () {
        return view('consultas');
    })->name('consultas');

    Route::get('/dictamen', [DictamenController::class, 'index'])->name('dictamen');
    Route::post('/dictamenes', [DictamenController::class, 'store'])->name('dictamenes.store');

    Route::get('/api/reportes/{id}', [ReporteController::class, 'showBasic'])
        ->name('reportes.lookup');

    // Route::get('/reportes/{reporte}/dictamen.pdf', [DictamenPdfController::class, 'show'])
    //     ->name('reportes.dictamen.pdf')
    //     ->middleware('permission:ImprimirDictamen');

    Route::middleware(['auth', 'permission:ImprimirDictamen'])
    ->get('/reportes/{reporte}/dictamen.pdf', [DictamenPdfController::class, 'show'])
    ->name('reportes.dictamen.pdf');

});
