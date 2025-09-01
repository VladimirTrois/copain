<?php

namespace App\Service;

use App\Dto\Shared\PaginatedResultDto;
use App\Mapper\PaginatedResultMapper;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{
    public function __construct(
        private PaginatedResultMapper $paginatedResultMapper,
    ) {
    }

    public function paginate(Query $query, int $page, int $limit): PaginatedResultDto
    {
        $query->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        $paginator = new Paginator($query);
        $items = iterator_to_array($paginator->getIterator());
        $total = count($paginator);

        $paginatedResultDto = $this->paginatedResultMapper->toDto($items, $page, $limit, $total);

        return $paginatedResultDto;
    }
}
