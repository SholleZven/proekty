<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductImportService implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Пропускаем заголовок, если он есть
        $headerSkipped = false;

        foreach ($rows as $row) {
            // Пропустить первую строку, если это заголовок
            if (!$headerSkipped && $row[0] === 'name' && $row[1] === 'rating') {
                $headerSkipped = true;
                continue;
            }

            // Создать или обновить запись
            Product::create([
                'name' => $row[0],
                'positive_conclusion' => is_numeric($row[1]) ? (int) $row[1] : null,
            ]);
        }
    }
}
