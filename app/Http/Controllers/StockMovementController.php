<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     *  Получение истории движения остатков товаров.
     *
     *  Метод возвращает список записей из таблицы stock_movements,
     *  связанных с изменениями остатков товаров на складах.
     *  Поддерживаются следующие фильтры:
     *  product_id (по ID товара)
     *  warehouse_id (по ID склада)
     *  date_from (дата начала периода, формат YYYY-MM-DD)
     *  date_to (дата конца периода, формат YYYY-MM-DD)
     *
     *  Также доступна настраиваемая пагинация через параметр per_page.
     *
     *  Связи:
     *  product: информация о товаре
     *  warehouse: информация о складе
     *  order: связанный заказ, если применимо
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with(['product', 'warehouse', 'order']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return response()->json(
            $query->latest()->paginate($request->get('per_page', 10))
        );
    }

}
