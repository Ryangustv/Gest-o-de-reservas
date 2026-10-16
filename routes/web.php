<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\EmpreendimentoController;


Route::post('/reservas',[ReservaController::class, 'cadastrarReserva'])->name('reservas.cadastrar-reserva');

Route::get('/reservas',[ReservaController::class, 'buscarReservasIndex'])->name('reservas.buscar-reservas-index');

Route::delete('/reservas/{id}', [ReservaController::class, 'deletarReserva'])->name('reservas.deletar-reserva');

Route::get('/', function() {
    return redirect()->route('empreendimentos.buscar-empreendimentos');
})->name('home');

Route::get('/empreendimentos', [EmpreendimentoController::class, 'buscarEmpreendimentos'])->name('empreendimentos.buscar-empreendimentos');
