<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new ProductImportService, $request->file('file'));

        return response()->json(['message' => 'Импорт завершён']);
    }

// public function upload(Request $request)
// {
//     try {
//         // Валидация входного файла
//         $request->validate([
//             'file' => 'required|file|mimes:xlsx,xls',
//         ]);

//         // Проверка на наличие файла и его корректность
//         $file = $request->file('file');

//         if (!$file || !$file->isValid()) {
//             return response()->json([
//                 'message' => 'Некорректный файл'
//             ], Response::HTTP_BAD_REQUEST);
//         }

//         // Попытка импорта
//         Excel::import(new ProductImportService, $file);

//         return response()->json([
//             'message' => 'Импорт завершён'
//         ], Response::HTTP_OK);

//     } catch (ValidationException $e) {
//         return response()->json([
//             'message' => 'Ошибка валидации',
//             'errors' => $e->errors()
//         ], Response::HTTP_UNPROCESSABLE_ENTITY);

//     } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
//         return response()->json([
//             'message' => 'Ошибка при импорте файла Excel',
//             'failures' => $e->failures(),
//         ], Response::HTTP_UNPROCESSABLE_ENTITY);

//     } catch (\Exception $e) {
//         // Логирование ошибки
//         Log::error('Ошибка при импорте файла: ' . $e->getMessage(), [
//             'trace' => $e->getTraceAsString(),
//         ]);

//         return response()->json([
//             'message' => 'Произошла ошибка на сервере',
//             'error' => $e->getMessage()
//         ], Response::HTTP_INTERNAL_SERVER_ERROR);
//     }
// }

}
