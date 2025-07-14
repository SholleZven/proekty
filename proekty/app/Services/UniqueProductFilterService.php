<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class UniqueProductFilterService
{
    /**
     * Фильтрует уникальные продукты:
     * - Удаляет дубли внутри Excel-файла (по project_number)
     * - Удаляет уже существующие в БД продукты (по project_number)
     */
    public function filter(Collection $rows): Collection
    {
        // Пропустить заголовок и привести project_number к строке
        $projectNumbersFromExcel = $rows
            ->skip(1)
            ->map(fn($row) => trim((string) $row[2]))
            ->filter()
            ->unique()
            ->values();

        // Получить уже существующие project_number из базы
        $existingNames = Product::whereIn('project_number', $projectNumbersFromExcel)->pluck('project_number')->toArray();
        $existingNamesSet = array_flip($existingNames);

        // Оставить только уникальные по project_number, которых нет в базе
        return $rows
            ->skip(1)
            ->filter(function ($row) use ($existingNamesSet) {
                $projectNumber = trim((string) $row[2]);
                return $projectNumber !== '' && !isset($existingNamesSet[$projectNumber]);
            })
            ->unique(fn($row) => trim((string) $row[2])) // убрать дубли внутри Excel
            ->values();
    }
}
