<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('/home');
});

Route::post('reservas',[ReservaController::class, 'cadastrarReserva'])->name('reservas.cadastrar-reserva');

Route::get('reservas',[ReservaController::class, 'buscarReservasIndex'])->name('reservas.buscar-reservas-index');

Route::delete('reservas/{id}', [ReservaController::class, 'deletarReserva'])->name('reservas.deletar-reserva');    

Route::get('empreendimentos', [EmpreendimentoController::class, 'buscarEmpreendimentos'])->name('empreendimentos.buscar-empreendimentos');