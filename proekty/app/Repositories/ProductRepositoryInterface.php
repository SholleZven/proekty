<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function getGroupedByInn(string $search, int $page, int $perPage): Collection;
    public function countUniqueInns(string $search): int;
}
