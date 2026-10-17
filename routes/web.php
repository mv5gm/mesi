<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ExistenciaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\OrdenVentaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TasaCambioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('can:gestionar usuarios')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/datos', [UserController::class, 'data'])->name('users.data');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('can:gestionar categorias')->group(function () {
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::get('/categorias/datos', [CategoriaController::class, 'data'])->name('categorias.data');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });

    Route::middleware('can:gestionar productos')->group(function () {
        Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/datos', [ProductoController::class, 'data'])->name('productos.data');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
    });

    Route::middleware('can:gestionar monedas')->group(function () {
        Route::get('/monedas', [MonedaController::class, 'index'])->name('monedas.index');
        Route::get('/monedas/datos', [MonedaController::class, 'data'])->name('monedas.data');
        Route::post('/monedas', [MonedaController::class, 'store'])->name('monedas.store');
        Route::put('/monedas/{moneda}', [MonedaController::class, 'update'])->name('monedas.update');
        Route::delete('/monedas/{moneda}', [MonedaController::class, 'destroy'])->name('monedas.destroy');
    });

    Route::middleware('can:gestionar tasas cambio')->group(function () {
        Route::get('/tasas-cambio', [TasaCambioController::class, 'index'])->name('tasas-cambio.index');
        Route::get('/tasas-cambio/datos', [TasaCambioController::class, 'data'])->name('tasas-cambio.data');
        Route::post('/tasas-cambio', [TasaCambioController::class, 'store'])->name('tasas-cambio.store');
        Route::put('/tasas-cambio/{tasaCambio}', [TasaCambioController::class, 'update'])->name('tasas-cambio.update');
        Route::delete('/tasas-cambio/{tasaCambio}', [TasaCambioController::class, 'destroy'])->name('tasas-cambio.destroy');
    });

    Route::middleware('can:gestionar almacenes')->group(function () {
        Route::get('/almacenes', [AlmacenController::class, 'index'])->name('almacenes.index');
        Route::get('/almacenes/datos', [AlmacenController::class, 'data'])->name('almacenes.data');
        Route::post('/almacenes', [AlmacenController::class, 'store'])->name('almacenes.store');
        Route::put('/almacenes/{almacen}', [AlmacenController::class, 'update'])->name('almacenes.update');
        Route::delete('/almacenes/{almacen}', [AlmacenController::class, 'destroy'])->name('almacenes.destroy');
    });

    Route::middleware('can:gestionar clientes')->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/datos', [ClienteController::class, 'data'])->name('clientes.data');
        Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
        Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    });

    Route::middleware('can:gestionar proveedores')->group(function () {
        Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/proveedores/datos', [ProveedorController::class, 'data'])->name('proveedores.data');
        Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    });

    Route::middleware('can:gestionar existencias')->group(function () {
        Route::get('/existencias', [ExistenciaController::class, 'index'])->name('existencias.index');
        Route::get('/existencias/datos', [ExistenciaController::class, 'data'])->name('existencias.data');
        Route::post('/existencias', [ExistenciaController::class, 'store'])->name('existencias.store');
        Route::put('/existencias/{existencia}', [ExistenciaController::class, 'update'])->name('existencias.update');
        Route::delete('/existencias/{existencia}', [ExistenciaController::class, 'destroy'])->name('existencias.destroy');
    });

    Route::middleware('can:gestionar compras')->group(function () {
        Route::get('/compras', [OrdenCompraController::class, 'index'])->name('compras.index');
        Route::get('/compras/datos', [OrdenCompraController::class, 'data'])->name('compras.data');
        Route::post('/compras', [OrdenCompraController::class, 'store'])->name('compras.store');
        Route::put('/compras/{ordenCompra}', [OrdenCompraController::class, 'update'])->name('compras.update');
        Route::delete('/compras/{ordenCompra}', [OrdenCompraController::class, 'destroy'])->name('compras.destroy');
    });

    Route::middleware('can:gestionar ventas')->group(function () {
        Route::get('/ventas', [OrdenVentaController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/datos', [OrdenVentaController::class, 'data'])->name('ventas.data');
        Route::post('/ventas', [OrdenVentaController::class, 'store'])->name('ventas.store');
        Route::put('/ventas/{ordenVenta}', [OrdenVentaController::class, 'update'])->name('ventas.update');
        Route::delete('/ventas/{ordenVenta}', [OrdenVentaController::class, 'destroy'])->name('ventas.destroy');
    });

    Route::middleware('can:gestionar metodos de pago')->group(function () {
        Route::get('/metodos-pago', [MetodoPagoController::class, 'index'])->name('metodos-pago.index');
        Route::get('/metodos-pago/datos', [MetodoPagoController::class, 'data'])->name('metodos-pago.data');
        Route::post('/metodos-pago', [MetodoPagoController::class, 'store'])->name('metodos-pago.store');
        Route::put('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'update'])->name('metodos-pago.update');
        Route::delete('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'destroy'])->name('metodos-pago.destroy');
    });
});
