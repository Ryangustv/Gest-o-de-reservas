<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_pagina_inicial_redireciona_para_empreendimentos(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/empreendimentos');
    }
}
