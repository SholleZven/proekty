<?php

namespace App\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

final class ProductRepository implements ProductRepositoryInterface
{

    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    public function getGroupedByInn(string $search, int $page, int $perPage, string $sortColumn, string $sortDirection): Collection
    {
        $offset = ($page - 1) * $perPage;

        $orderableMap = [
            'name'                               => 'LOWER(b.name)', // регистронезависимая сортировка
            'inn'                                => 'b.inn',
            'quantity_conclusions'               => 'b.quantity_conclusions',
            'quantity_positive_conclusion'       => 'b.quantity_positive_conclusion',
            'quantity_negative_conclusion'       => 'b.quantity_negative_conclusion',
            'average_expertise_date'             => 'b.average_expertise_date',
            'average_complect_date'              => 'b.average_complect_date',
            'most_common_functional_purpose'     => 'LOWER(b.most_common_functional_purpose)',
            'most_common_stage_construction_works' => 'LOWER(b.most_common_stage_construction_works)',
            'rating'                             => 'rating',
        ];

        $orderExpr = $orderableMap[$sortColumn] ?? 'rating';
        $direction = strtolower($sortDirection) === 'asc' ? 'ASC' : 'DESC';

        $items = $this->connection->select(
            "
            WITH base AS (
                SELECT
                    p.inn,
                    MIN(p.name) AS name,
                    COUNT(*) AS quantity_conclusions,
                    SUM((p.conclusion_result = 'Положительное')::int) AS quantity_positive_conclusion,
                    SUM((p.conclusion_result = 'Отрицательное')::int) AS quantity_negative_conclusion,
                    CEIL(AVG(p.conclusion_date - p.contract_date)) AS average_expertise_date,
                    CEIL(AVG(p.contract_date - p.registration_date)) AS average_complect_date
                FROM products p
                WHERE p.inn IS NOT NULL
                AND p.inn <> ''
                AND (
                    :search = ''
                    OR p.name ILIKE :searchPattern
                    OR p.inn ILIKE :searchPattern
                )
                GROUP BY p.inn
            ),
            common_fp AS (
                SELECT DISTINCT ON (inn)
                    inn, functional_purpose
                FROM (
                    SELECT inn, functional_purpose, COUNT(*) AS cnt
                    FROM products
                    WHERE inn IS NOT NULL AND inn <> ''
                    GROUP BY inn, functional_purpose
                    ORDER BY inn, cnt DESC
                ) t
            ),
            common_stage AS (
                SELECT DISTINCT ON (inn)
                    inn, stage_construction_works
                FROM (
                    SELECT inn, stage_construction_works, COUNT(*) AS cnt
                    FROM products
                    WHERE inn IS NOT NULL AND inn <> ''
                    GROUP BY inn, stage_construction_works
                    ORDER BY inn, cnt DESC
                ) t
            ),
            max_vals AS (
                SELECT MAX(count_per_inn) AS max_count
                FROM (
                    SELECT COUNT(*) AS count_per_inn
                    FROM products
                    WHERE inn IS NOT NULL AND inn <> ''
                    GROUP BY inn
                ) sub
            )
            SELECT
                b.*,
                fp.functional_purpose AS most_common_functional_purpose,
                st.stage_construction_works AS most_common_stage_construction_works,
                ROUND(
                    0.8 * (
                        (b.quantity_positive_conclusion - 2 * b.quantity_negative_conclusion)::numeric
                        / NULLIF(b.quantity_conclusions, 0)
                    ) * 100
                    +
                    0.2 * (b.quantity_conclusions::numeric / NULLIF(m.max_count, 0)) * 100,
                    2
                ) AS rating
            FROM base b
            LEFT JOIN common_fp fp ON fp.inn = b.inn
            LEFT JOIN common_stage st ON st.inn = b.inn
            CROSS JOIN max_vals m
            ORDER BY {$orderExpr} {$direction} NULLS LAST
            LIMIT :perPage OFFSET :offset;
        ",
            [
                'search'        => $search,
                'searchPattern' => '%' . $search . '%',
                'perPage'       => $perPage,
                'offset'        => $offset
            ]
        );

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

    public function getGroupedByName(string $inn, int $page, int $perPage): Collection
    {
        $offset = ($page - 1) * $perPage;

        $items = $this->connection->select("
            SELECT
                p.inn,
                project_name,
                object_type,
                functional_purpose,
                service_type,
                conclusion_date,
                conclusion_result,
                cost_declared,
                cost_adjusted,
                stage_construction_works,
                conclusion_date - contract_date AS expertise_date,
                contract_date - registration_date AS complect_date

            FROM products p
            WHERE p.inn IS NOT NULL
            AND p.inn != ''
            AND p.inn = :inn

            LIMIT :perPage OFFSET :offset
    ", [
            'inn'          =>  $inn,
            'perPage'       => $perPage,
            'offset'        => $offset
        ]);

        return collect($items);
    }

    public function countUniqueNames(string $inn): int
    {
        $result = $this->connection->selectOne(
            "
            SELECT COUNT(*) AS total
            FROM products p
            WHERE p.inn IS NOT NULL
              AND p.inn != ''
              AND p.inn = :inn
        ",
            [
                'inn'          => $inn
            ]
        );

        return (int) $result->total;
    }
}
