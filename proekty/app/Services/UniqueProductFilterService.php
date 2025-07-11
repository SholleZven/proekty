<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class UniqueProductFilterService
{
    /**
     * Фильтрует уникальные продукты:
     * - Удаляет дубли внутри Excel-файла (по name)
     * - Удаляет уже существующие в БД продукты (по name)
     */
    public function filter(Collection $rows): Collection
    {
        // Пропустить заголовок и привести name к строке
        $namesFromExcel = $rows
            ->skip(1)
            ->map(fn($row) => trim((string) $row[0]))
            ->filter()
            ->unique()
            ->values();

        // Получить уже существующие name из базы
        $existingNames = Product::whereIn('name', $namesFromExcel)->pluck('name')->toArray();
        $existingNamesSet = array_flip($existingNames);

        // Оставить только уникальные по name, которых нет в базе
        return $rows
            ->skip(1)
            ->filter(function ($row) use ($existingNamesSet) {
                $name = trim((string) $row[0]);
                return $name !== '' && !isset($existingNamesSet[$name]);
            })
            ->unique(fn($row) => trim((string) $row[0])) // убрать дубли внутри Excel
            ->values();
    }
}
