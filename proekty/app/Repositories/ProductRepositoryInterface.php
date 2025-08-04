<?php

namespace App\Repositories;

interface ProductRepositoryInterface
{
    public function searchWithPagination(string $search, int $page, int $perPage);
}
