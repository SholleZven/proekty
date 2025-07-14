<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductImportService;
use App\Services\UniqueProductFilterService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function fetch()
    {
        return response()->json(Product::paginate(10));
    }

    public function upload(Request $request)
    {
            // Валидация входного файла
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls',
            ]);

            // Проверка на наличие файла и его корректность
            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                return response()->json([
                    'message' => 'Некорректный файл'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Создаём сервисы фильтрации и импорта
            $importService = new ProductImportService(new UniqueProductFilterService());

            // Импортируем файл
            Excel::import($importService, $file);

            return response()->json([
                'message' => 'Импорт завершён'
            ], Response::HTTP_OK);
        }
}
