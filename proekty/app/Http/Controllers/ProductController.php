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

        $cacheKey = "products_grouped_inn:" . md5($search) . "_page:" . $page;

        $cached = Cache::tags(['products'])->remember($cacheKey, 60 * 30, function () use ($repository, $search, $page, $perPage) {
            $items = $repository->getGroupedByInn($search, $page, $perPage);
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

        // Сбросить весь кэш с тегом 'products'
        Cache::tags(['products'])->flush();

        return response()->json([
            'message' => 'Импорт завершён',
        ], Response::HTTP_OK);
    }
}
