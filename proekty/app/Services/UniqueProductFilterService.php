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
        // Пропустить заголовок и привести egrz_number к строке
        $egrzNumbersFromExcel = $rows
            ->skip(1)
            ->map(fn($row) => trim((string) $row[2]))
            ->filter()
            ->unique()
            ->values();

        // Получить уже существующие egrz_number из базы
        $existingNames = Product::whereIn('egrz_number', $egrzNumbersFromExcel)->pluck('egrz_number')->toArray();
        $existingNamesSet = array_flip($existingNames);

        // Оставить только уникальные по egrz_number, которых нет в базе
        return $rows
            ->skip(1)
            ->filter(function ($row) use ($existingNamesSet) {
                $egrzNumber = trim((string) $row[2]);
                return $egrzNumber !== '' && !isset($existingNamesSet[$egrzNumber]);
            })
            ->unique(fn($row) => trim((string) $row[2])) // убрать дубли внутри Excel
            ->values();
    }
}
