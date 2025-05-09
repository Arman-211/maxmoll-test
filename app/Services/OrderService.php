<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Создание заказа с проверкой и списанием остатков.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::query()->create([
                'customer' => $data['customer'],
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'active',
            ]);

            foreach ($data['items'] as $item) {
                $stock = Stock::query()->where('product_id', $item['product_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->stock < $item['count']) {
                    throw new \Exception('Недостаточно товара на складе.');
                }

                $stock->decrement('stock', $item['count']);
                $order->items()->create($item);

                StockMovement::query()->create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'delta' => -$item['count'],
                    'operation_type' => 'order_created',
                    'order_id' => $order->id,
                ]);
            }

            return $order;
        });
    }

    /**
     * Обновление заказа: возврат старых остатков, списание новых.
     *
     * @param Order $order
     * @param array $data
     * @return Order
     */
    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            foreach ($order->items as $oldItem) {
                Stock::query()->where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->increment('stock', $oldItem->count);

                StockMovement::query()->create([
                    'product_id' => $oldItem->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'delta' => $oldItem->count,
                    'operation_type' => 'order_update_revert',
                    'order_id' => $order->id,
                ]);
            }

            $order->items()->delete();

            foreach ($data['items'] as $item) {
                $stock = Stock::query()->where('product_id', $item['product_id'])
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->stock < $item['count']) {
                    throw new \Exception('Недостаточно товара.');
                }

                $stock->decrement('stock', $item['count']);
                $order->items()->create($item);

                StockMovement::query()->create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $order->warehouse_id,
                    'delta' => -$item['count'],
                    'operation_type' => 'order_updated',
                    'order_id' => $order->id,
                ]);
            }

            $order->update(['customer' => $data['customer']]);

            return $order;
        });
    }


    /**
     * Отмена заказа с возвратом остатков.
     *
     * @param Order $order
     * @return void
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Stock::query()->where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->increment('stock', $item->count);

                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'delta' => $item->count,
                    'operation_type' => 'order_canceled',
                    'order_id' => $order->id,
                ]);
            }

            $order->update(['status' => 'canceled']);
        });
    }

    /**
     * Возобновление отменённого заказа с повторным списанием остатков.
     *
     * @param Order $order
     * @return void
     */
    public function resume(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = Stock::query()->where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->stock < $item->count) {
                    throw new \Exception('Недостаточно товара для возобновления.');
                }

                $stock->decrement('stock', $item->count);

                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'delta' => -$item->count,
                    'operation_type' => 'order_resumed',
                    'order_id' => $order->id,
                ]);
            }

            $order->update(['status' => 'active']);
        });
    }

    /**
     * Завершение заказа.
     *
     * @param Order $order
     * @return void
     */
    public function complete(Order $order): void
    {
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Изменение статуса заказа.
     *
     * @param Order $order
     * @param string $status
     * @return void
     */
    public function changeStatus(Order $order, string $status): void
    {
        match ($status) {
            'cancel' => $this->cancel($order),
            'resume' => $this->resume($order),
            'complete' => $this->complete($order),
            default => throw new \InvalidArgumentException('Недопустимый статус действия.'),
        };
    }
}

