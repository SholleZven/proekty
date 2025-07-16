<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
            // $name = trim((string) $row[0]);
            $registrationDate = Carbon::instance(Date::excelToDateTimeObject($row[0]));
            $projectNumber = trim((string) $row[1]);
            $egrzNumber = trim((string) $row[2]);
            $projectName = trim((string) $row[3]);
            $objectType = trim((string) $row[4]);
            $functionalPurpose = trim((string) $row[5]);
            $serviceType = trim((string) $row[6]);
            $startDate = Carbon::instance(Date::excelToDateTimeObject($row[7]));
            $name = trim((string) $row[8]);
            $inn = trim((string) $row[9]);
            $contractDate = Carbon::instance(Date::excelToDateTimeObject($row[10]));
            $conclusionDate = Carbon::instance(Date::excelToDateTimeObject($row[11]));
            $conclusionResult = trim((string) $row[12]);
            $projectStatus = trim((string) $row[13]);
            $costDeclared = $row[14];
            $costAdjusted = $row[15];
            $stageConstructionWorks = trim((string) $row[16]);

            // $projectNumber = $row[2];
            // $positiveConclusion = $row[1];
            Product::create([
                'registration_date' => $registrationDate,
                'project_number' => $projectNumber,
                'egrz_number' => $egrzNumber,
                'project_name' => $projectName,
                'object_type' => $objectType,
                'functional_purpose' => $functionalPurpose,
                'service_type' => $serviceType,
                'start_date' => $startDate,
                'name' => $name,
                'inn' => $inn,
                'contract_date' => $contractDate,
                'conclusion_date' => $conclusionDate,
                'conclusion_result' => $conclusionResult,
                'project_status' => $projectStatus,
                'cost_declared' => is_numeric($costDeclared) ? (float) $costDeclared : null,
                'cost_adjusted' => is_numeric($costAdjusted) ? (float) $costAdjusted : null,
                'stage_construction_works' => $stageConstructionWorks
                // 'positive_conclusion' => is_numeric($positiveConclusion) ? (int) $positiveConclusion : null,
            ]);
        }
    }
}
