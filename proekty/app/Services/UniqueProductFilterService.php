<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class UniqueProductFilterService
{
    /**
     * Фильтрует уникальные продукты:
     * - Удаляет дубли внутри Excel-файла (по egrz_number)
     * - Удаляет уже существующие в БД продукты (по egrz_number)
     */
    public function filter(Collection $rows): Collection
{
    $rows = $rows->skip(1); // Пропускаем заголовок

    // Собираем egrz_number и project_number
    $egrzNumbersFromExcel = $rows->map(fn($row) => trim((string) $row[2]))->filter()->unique()->values();
    $projectNumbersFromExcel = $rows->map(fn($row) => trim((string) $row[1]))->filter()->unique()->values();

    $existingEgrz = Product::whereIn('egrz_number', $egrzNumbersFromExcel)->pluck('egrz_number')->toArray();
    $existingProjects = Product::whereIn('project_number', $projectNumbersFromExcel)->pluck('project_number')->toArray();

    $existingEgrzSet = array_flip($existingEgrz);
    $existingProjectsSet = array_flip($existingProjects);

    return $rows->filter(function ($row) use ($existingEgrzSet, $existingProjectsSet) {
        $egrzNumber = trim((string) $row[2]);
        $projectNumber = trim((string) $row[1]);
        $inn = trim((string) $row[9]); // колонка ИНН

        return $egrzNumber !== ''
            && !isset($existingEgrzSet[$egrzNumber])
            && $projectNumber !== ''
            && !isset($existingProjectsSet[$projectNumber])
            && $inn !== ''; // Исключить строки с пустым ИНН
    })
    ->unique(fn($row) => trim((string) $row[2]) . '|' . trim((string) $row[1])) // убрать дубли
    ->values();
}

}
