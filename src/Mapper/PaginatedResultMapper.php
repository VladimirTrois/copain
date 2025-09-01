<?php

namespace App\Mapper;

use App\Dto\Shared\PaginatedResultDto;

class PaginatedResultMapper
{
    /**
     * @param array<mixed> $items
     */
    public function toDto(array $items, int $page, int $limit, int $total): PaginatedResultDto
    {
        return new PaginatedResultDto($items, $page, $limit, $total);
    }
}
