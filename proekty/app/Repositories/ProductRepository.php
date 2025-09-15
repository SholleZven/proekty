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
            'name'                               => 'LOWER(b.name)',
            'inn'                                => 'b.inn',
            'quantity_conclusions'               => 'b.quantity_conclusions',
            'quantity_positive_conclusion'       => 'b.quantity_positive_conclusion',
            'quantity_negative_conclusion'       => 'b.quantity_negative_conclusion',
            'average_expertise_date'             => 'b.average_expertise_date',
            'average_complect_date'              => 'b.average_complect_date',
            'most_common_functional_purpose'     => 'LOWER(b.most_common_functional_purpose)',
            'most_common_stage_construction_works' => 'LOWER(b.most_common_stage_construction_works)',
            'rating'                             => 'rating',
            'rating_rank'                        => 'rating_rank',
        ];

        $orderExpr = $orderableMap[$sortColumn] ?? 'rating';
        $direction = strtolower($sortDirection) === 'asc' ? 'ASC' : 'DESC';

        $items = $this->connection->select(
            "
            WITH base_all AS (
                SELECT
                    p.inn,
                    MIN(p.name) AS name,
                    COUNT(*) AS quantity_conclusions,
                    SUM((p.conclusion_result = 'Положительное')::int) AS quantity_positive_conclusion,
                    SUM((p.conclusion_result = 'Отрицательное')::int) AS quantity_negative_conclusion,
                    CEIL(AVG(
                            CASE
                                WHEN p.contract_date = '1970-01-01' THEN p.conclusion_date - p.registration_date
                                ELSE p.conclusion_date - p.contract_date
                            END
                        )) AS average_expertise_date,
                    CEIL(AVG(
                            CASE
                                WHEN p.contract_date = '1970-01-01' THEN p.start_date - p.registration_date
                                ELSE p.contract_date - p.registration_date
                            END
                        )) AS average_complect_date
                FROM products p
                WHERE p.inn IS NOT NULL AND p.inn <> ''
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
                SELECT
                    inn,
                    stage_construction_works,
                    cnt,
                    ROW_NUMBER() OVER (PARTITION BY inn ORDER BY cnt DESC) AS rn
                FROM (
                    SELECT inn, stage_construction_works, COUNT(*) AS cnt
                    FROM products
                    WHERE inn IS NOT NULL AND inn <> ''
                    GROUP BY inn, stage_construction_works
                ) t
            ),
            scored_all AS (
                SELECT
                    b.*,
                    fp.functional_purpose AS most_common_functional_purpose,
                    st1.stage_construction_works AS most_common_stage_construction_works,
                    st2.stage_construction_works AS second_common_stage_construction_works,
                    ROUND(
                        (
                            0.6 * COALESCE(LN(NULLIF(b.quantity_positive_conclusion,0)::double precision),0)
                        + 0.2 * ((b.quantity_positive_conclusion + 1)::numeric
                                / NULLIF(b.quantity_positive_conclusion + b.quantity_negative_conclusion + 2,0))
                        + 0.2 * ((b.quantity_positive_conclusion + 1)::numeric
                                / NULLIF(b.quantity_negative_conclusion + 1,0))
                        )::numeric, 6
                    ) AS rating
                FROM base_all b
                LEFT JOIN common_fp fp ON fp.inn = b.inn
                LEFT JOIN common_stage st1 ON st1.inn = b.inn AND st1.rn = 1
                LEFT JOIN common_stage st2 ON st2.inn = b.inn AND st2.rn = 2
            ),
            ranked_all AS (
                SELECT
                    s.*,
                    RANK() OVER (ORDER BY s.rating DESC NULLS LAST) AS rating_rank
                FROM scored_all s
            )
            SELECT *
            FROM ranked_all b
            WHERE :search = ''
            OR b.name ILIKE :searchPattern
            OR b.inn ILIKE :searchPattern
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
