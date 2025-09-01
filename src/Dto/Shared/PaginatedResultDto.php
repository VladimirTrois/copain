<?php

namespace App\Dto\Shared;

class PaginatedResultDto
{
    public int $totalPages;

    public bool $hasNextPage;

    public bool $hasPreviousPage;

    /**
     * @param array<mixed> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $limit,
        public int $totalItems,
    ) {
        $this->totalPages = (int) ceil($totalItems / $limit);
        $this->hasPreviousPage = $page > 1;
        $this->hasNextPage = $page < $this->totalPages;
    }
}
