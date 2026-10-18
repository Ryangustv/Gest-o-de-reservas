<?php

namespace App\OpenAPI;

use OpenApi\Attributes as OA;

class EmpreendimentoDocumentation
{
   #[OA\Get(
    path: '/empreendimentos',
    summary: 'Listar empreendimentos e unidades',
    tags: ['Empreendimentos'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Listagem de empreendimentos',
            content: new OA\JsonContent(type: 'object')
        )
    ]
   )] 
   public function buscarEmpreendimentos(): void
   {}
}