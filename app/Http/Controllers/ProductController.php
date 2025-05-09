<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{

    /**
     * Получение списка всех продуктов с их остатками по складам.
     *
     * @return JsonResponse
     */
    public function indexWithStocks(): JsonResponse
    {
        return response()->json(
            Product::with('stocks.warehouse')->get()
        );
    }
}

