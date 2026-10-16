<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Repositories\Manager\EmpreendimentoRepository;
use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;
use App\Repositories\Manager\ReservaRepository;
use App\Repositories\Interfaces\UnidadeRepositoryInterface;
use App\Repositories\Manager\UnidadeRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ReservaRepositoryInterface::class, ReservaRepository::class);
        $this->app->bind(EmpreendimentoRepositoryInterface::class, EmpreendimentoRepository::class);
        $this->app->bind(UnidadeRepositoryInterface::class, UnidadeRepository::class);
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
