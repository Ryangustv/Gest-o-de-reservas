<?php

namespace App\OpenAPI;

use OpenApi\Attributes as OA;

class EmpreendimentoDocumentation
{
   #[OA\Get(
    path: '/empreendimentos',
    summary: 'Listar empreendimentos e unidades',
    tags: ['Empreendimentos'],
    parameters: [
        new OA\Parameter(
            name: 'atualizar',
            in: 'query',
            description: 'Invalida o cache e busca os dados atuais no banco.',
            required: false,
            schema: new OA\Schema(type: 'boolean', default: false)
        )
    ],
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
