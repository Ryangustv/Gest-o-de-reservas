<?php

namespace App\OpenAPI;

use OpenApi\Attributes as OA;

class ReservaDocumentation
{
  #[OA\Post(
    path: '/reservas',
    summary: 'Cadastrar reservas',
    tags: ['Reservas'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Cadastro de reservas',
            content: new OA\JsonContent(type: 'object')
        )
    ],
    requestBody: new OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
        type: 'object',
        required: ['unidade_id', 'cliente_nome', 'cliente_email'],
        properties: [
            new OA\Property(
                property: 'unidade_id',
                type: 'integer',
                example: 1
            ),
            new OA\Property(
                property: 'cliente_nome',
                type: 'string',
                maxLength: 100,
                example: 'Ana Silva'
            ),
            new OA\Property(
                property: 'cliente_email',
                type: 'string',
                format: 'email',
                maxLength: 100,
                example: 'ana@example.com'
            )
        ]
    )
    ),
   )] 
    public function cadastrarReserva(): void
    {}

    #[OA\Get(
    path: '/reservas',
    summary: 'Listar reservas',
    tags: ['Reservas'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Listagem de reservas',
            content: new OA\JsonContent(type: 'object')
        )
    ]
    )] 
    public function buscarReservasIndex(): void 
    {}

    #[OA\Delete(
    path: '/reservas/{reservaId}',
    summary: 'Cancelar reservas',
    tags: ['Reservas'],
    parameters: [
        new OA\Parameter(
            name: 'reservaId',
            in: 'path',
            required: true,
            description: 'ID da reserva a ser cancelada',
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(response: 200, description: 'Reserva cancelada'),
        new OA\Response(response: 404, description: 'Reserva não encontrada')
    ]
    )] 
    public function deletarReserva(): void
    {}
}