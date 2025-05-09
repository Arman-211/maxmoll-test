<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * @param OrderService $orderService
     */
    public function __construct(private OrderService $orderService){}

    /**
     *  Список заказов с фильтрацией по статусу и пагинацией.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $orders = $this->orderService->getFilteredList($request->only(['status', 'per_page']));

            return response()->json($orders);
        } catch (\Throwable $e) {
            Log::error('Ошибка при получении списка заказов', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ошибка при получении заказов'], 500);
        }
    }

    /**
     * Создание нового заказа.
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->create($request->validated());
            return response()->json($order, 201);
        } catch (\Throwable $e) {
            Log::error('Ошибка при создании заказа', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ошибка при создании заказа'], 500);
        }
    }

    /**
     * Обновление существующего заказа.
     *
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @return JsonResponse
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        if (in_array($order->status, ['completed', 'canceled'])) {
            return response()->json(['error' => 'Нельзя редактировать завершённый или отменённый заказ'], 400);
        }

        try {
            $updatedOrder = $this->orderService->update($order, $request->validated());
            return response()->json($updatedOrder);
        } catch (\Throwable $e) {
            Log::error('Ошибка при обновлении заказа', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ошибка при обновлении заказа'], 500);
        }
    }

    /**
     * Смена статуса заказа: завершение, отмена или возобновление.
     *
     * Обрабатывает запрос на смену статуса для переданного заказа.
     * Поддерживаемые значения статуса: 'complete', 'cancel', 'resume'.
     * В случае некорректного статуса или логической ошибки возвращает код 400.
     * В случае внутренних сбоев — код 500.
     *
     * @param Order $order
     * @param string $status
     * @return JsonResponse
     */
    public function changeStatus(Order $order, string $status): JsonResponse
    {
        try {
            $this->orderService->changeStatus($order, $status);

            return response()->json(['message' => "Статус заказа обновлён: {$status}"]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

}


