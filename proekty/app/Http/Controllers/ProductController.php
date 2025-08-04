<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductImportService;
use App\Services\UniqueProductFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function fetch(Request $request)
    {
        $search = trim($request->input('search', ''));
        $page = max((int) $request->input('page', 1), 1);
        $perPage = 10;

        $cacheKey = "products_grouped_inn:" . md5($search) . "_page:" . $page;

        $cached = Cache::tags(['products'])->remember($cacheKey, 60 * 30, function () use ($search, $page, $perPage) {
            // 1. Получить все уникальные INN с учётом поиска
            $uniqueInns = Product::query()
                ->when($search, fn($q) => $q->where('name', 'ILIKE', "%$search%"))
                ->whereNotNull('inn')
                ->where('inn', '!=', '')
                ->select('inn')
                ->distinct()
                ->orderBy('inn')
                ->pluck('inn')
                ->toArray();

            $total = count($uniqueInns);

            // 2. Разбить на страницы вручную
            $currentInns = array_slice($uniqueInns, ($page - 1) * $perPage, $perPage);

            // 3. Получить по одному продукту на каждый INN
            $items = Product::query()
                ->whereIn('inn', $currentInns)
                ->whereNotNull('inn')
                ->where('inn', '!=', '')
                ->when($search, fn($q) => $q->where('name', 'ILIKE', "%$search%"))
                ->groupBy('inn')
                ->selectRaw('MIN(id) as id') // один продукт на inn
                ->pluck('id');

            $products = Product::whereIn('id', $items)
                ->orderBy('inn')
                ->get();

            return new LengthAwarePaginator(
                $products,
                $total, // общее число уникальных inn
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        });

        return response()->json($cached);
    }

    public function fetchByName($name, Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = 10;

        $cacheKey = "products_by_name:" . md5($name) . "_page:" . $page;

        $cached = Cache::tags(['products'])->remember($cacheKey, 60 * 30, function () use ($name, $page, $perPage) {
            return Product::where('name', $name)->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json($cached);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            return response()->json([
                'message' => 'Некорректный файл',
            ], Response::HTTP_BAD_REQUEST);
        }

        $importService = new ProductImportService(new UniqueProductFilterService());

        Excel::import($importService, $file);

        // Сбросить весь кэш с тегом 'products'
        Cache::tags(['products'])->flush();

        return response()->json([
            'message' => 'Импорт завершён',
        ], Response::HTTP_OK);
    }
}
