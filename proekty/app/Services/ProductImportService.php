<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductImportService implements ToCollection
{
    protected UniqueProductFilterService $filterService;

    public function __construct(UniqueProductFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function collection(Collection $rows): void
    {
        $filteredRows = $this->filterService->filter($rows);

        foreach ($filteredRows as $row) {
            $name = trim((string) $row[0]);
            $positiveConclusion = $row[1];
            $projectNumber = $row[2];

            Product::create([
                'name' => $name,
                'positive_conclusion' => is_numeric($positiveConclusion) ? (int) $positiveConclusion : null,
                'project_number' => $projectNumber
            ]);
        }
    }
}
