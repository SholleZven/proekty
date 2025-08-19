<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function getGroupedByInn(string $search, int $page, int $perPage, string $sortColumn, string $sortDirection): Collection;
    public function countUniqueInns(string $search): int;
    public function getGroupedByName(string $inn, int $page, int $perPage): Collection;
    public function countUniqueNames(string $inn): int;
}
