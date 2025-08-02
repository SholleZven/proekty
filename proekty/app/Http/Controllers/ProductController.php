<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductImportService;
use App\Services\UniqueProductFilterService;
use Illuminate\Http\Request;
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
        $page = (int) $request->input('page', 1);
        $perPage = 10;

        // Уникальный ключ по параметрам поиска и страницы
        $cacheKey = "products_search:" . md5($search) . "_page:" . $page;

        $cached = Cache::tags(['products'])->remember($cacheKey, 60 * 30, function () use ($search, $page, $perPage) {
            $query = Product::query();

            if (!empty($search)) {
                $query->where('name', 'ILIKE', '%' . $search . '%');
            }

            return $query->paginate($perPage, ['*'], 'page', $page);
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
