<?php

namespace App\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

final class ProductRepository implements ProductRepositoryInterface
{

    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    public function getGroupedByInn(string $search, int $page, int $perPage): Collection
    {
        $offset = ($page - 1) * $perPage;

        $items = $this->connection->select("
            SELECT
                MIN(p.name) AS name,
                p.inn,
                COUNT(p.conclusion_result) AS quantity_conclusions,
                SUM(CASE WHEN p.conclusion_result = 'Положительное' THEN 1 ELSE 0 END) AS quantity_positive_conclusion,
                SUM(CASE WHEN p.conclusion_result = 'Отрицательное' THEN 1 ELSE 0 END) AS quantity_negative_conclusion,
                CEIL(AVG(conclusion_date - contract_date)) as average_expertise_date,
                CEIL(AVG(contract_date - registration_date)) as average_complect_date,
                (
                    SELECT fp.functional_purpose
                    FROM products fp
                    WHERE fp.inn = p.inn
                    GROUP BY fp.functional_purpose
                    ORDER BY COUNT(*) DESC
                    LIMIT 1
                )
                AS most_common_functional_purpose,
                (
                    SELECT fp.stage_construction_works
                    FROM products fp
                    WHERE fp.inn = p.inn
                    GROUP BY fp.stage_construction_works
                    ORDER BY COUNT(*) DESC
                    LIMIT 1
                )
                AS most_common_stage_construction_works

            FROM products p
            WHERE p.inn IS NOT NULL
            AND p.inn != ''
            AND (
                :search = ''
                OR p.name ILIKE :searchPattern
                OR p.inn ILIKE :searchPattern
            )
            GROUP BY p.inn
            ORDER BY p.inn
            LIMIT :perPage OFFSET :offset
    ", [
            'search'        => $search,
            'searchPattern' => '%' . $search . '%',
            'perPage'       => $perPage,
            'offset'        => $offset
        ]);

        return collect($items);
    }

    public function countUniqueInns(string $search): int
    {
        $result = $this->connection->selectOne("
            SELECT COUNT(DISTINCT p.inn) AS total
            FROM products p
            WHERE p.inn IS NOT NULL
              AND p.inn != ''
              AND (:search = '' OR p.name ILIKE :searchPattern)
        ", [
            'search'        => $search,
            'searchPattern' => "%{$search}%"
        ]);

        return (int) $result->total;
    }
}
