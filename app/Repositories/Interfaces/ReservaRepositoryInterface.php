<?php

namespace App;

interface ReservaRepositoryInterface
{
    public function delete(int $reservaId): JsonResponse;

    public function findById(int $reservaId): ?Reserva;

    public function findAll(): Response;
}
