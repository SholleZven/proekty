<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
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

    public function fetch(Request $request, ProductRepositoryInterface $repository)
    {
        $search  = trim($request->input('search', ''));
        $page    = max((int) $request->input('page', 1), 1);
        $perPage = 10;

        // Белый список колонок, по которым разрешаем сортировку

        $allowedSort = [
            'name',
            'inn',
            'quantity_conclusions',
            'quantity_positive_conclusion',
            'quantity_negative_conclusion',
            'average_expertise_date',
            'average_complect_date',
            'most_common_functional_purpose',
            'most_common_stage_construction_works',
            'rating',
            'rating_rank'
        ];

        $sortColumn    = $request->input('sortColumn');
        $sortDirection = strtolower($request->input('sortDirection', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Значения по умолчанию (сортируем по рейтингу убыв.)

        if (!in_array($sortColumn, $allowedSort, true)) {
            $sortColumn = 'rating';
            $sortDirection = 'desc';
        }

        $cacheKey = sprintf(
            'products_grouped_inn:%s:%s:%s_page:%d_per:%d',
            md5($search),
            $sortColumn,
            $sortDirection,
            $page,
            $perPage
        );

        $cached = Cache::tags(['products'])->remember($cacheKey, 60 * 30, function () use ($repository, $search, $page, $perPage, $sortColumn, $sortDirection) {
            $items = $repository->getGroupedByInn($search, $page, $perPage, $sortColumn, $sortDirection);
            $total = $repository->countUniqueInns($search);

            return new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        });

        return response()->json($cached);
    }


    public function fetchByInn($name, Request $request, ProductRepository $repository)
    {
        $page = (int) $request->input('page', 1);
        $perPage = 3;

        $cacheKey = "products_by_name:" . md5($name) . "_page:" . $page;

        $cached = Cache::tags(['products'])->remember($cacheKey, 60 * 30, function () use ($name, $page, $perPage, $repository) {
            $items = $repository->getGroupedByName($name, $page, $perPage);
            $total = $repository->countUniqueNames($name);

            return new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
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

        // Сбросить весь кэш
        Cache::flush();

        return response()->json([
            'message' => 'Импорт завершён',
        ], Response::HTTP_OK);
    }
}
