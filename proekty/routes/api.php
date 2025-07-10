<?php

use App\Models\Product;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/products', fn () => Product::all());

Route::post('/upload', function (Request $request) {
    $request->validate(['file' => 'required|mimes:xlsx,xls']);

    Excel::import(new ProductsImport, $request->file('file'));
    return response()->json(['message' => 'Импорт завершён']);
});
