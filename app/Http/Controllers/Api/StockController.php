<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStockInRequest;
use App\Http\Requests\StoreStockOutRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;


class StockController extends Controller
{
    public function stockIn(StoreStockInRequest $request): JsonResponse
    {
        $transaction = StockTransaction::create([
            'product_id'  => $request->product_id,
            'supplier_id' => $request->supplier_id,
            'type'        => 'in',
            'quantity'    => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Stock added successfully.',
            'data'    => $transaction
        ], 201);
    }

       public function stockOut(StoreStockOutRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->product_id);

        $stockIn = $product->stockTransactions()
            ->where('type', 'in')
            ->sum('quantity');

        $stockOut = $product->stockTransactions()
            ->where('type', 'out')
            ->sum('quantity');

        $currentStock = $stockIn - $stockOut;

        if ($request->quantity > $currentStock) {
            return response()->json([
                'message' => 'Insufficient stock.'
            ], 422);
        }

        $transaction = StockTransaction::create([
            'product_id'  => $request->product_id,
            'supplier_id' => null,
            'type'        => 'out',
            'quantity'    => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Stock removed successfully.',
            'data'    => $transaction
        ]);
    }
    public function currentStock(Product $product): JsonResponse
{
    $stockIn = $product->stockTransactions()
        ->where('type', 'in')
        ->sum('quantity');

    $stockOut = $product->stockTransactions()
        ->where('type', 'out')
        ->sum('quantity');

    $currentStock = $stockIn - $stockOut;

    return response()->json([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'stock_in' => $stockIn,
        'stock_out' => $stockOut,
        'current_stock' => $currentStock,
    ]);
}
public function lowStock(): JsonResponse
{
    $products = Product::with('stockTransactions')->get();

    $lowStockProducts = [];

    foreach ($products as $product) {

        $stockIn = $product->stockTransactions
            ->where('type', 'in')
            ->sum('quantity');

        $stockOut = $product->stockTransactions
            ->where('type', 'out')
            ->sum('quantity');

        $currentStock = $stockIn - $stockOut;

        if ($currentStock <= 10) {
            $lowStockProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'current_stock' => $currentStock,
            ];
        }
    }

    return response()->json($lowStockProducts);
}
public function transactions(): JsonResponse
{
    $transactions = StockTransaction::with([
        'product',
        'supplier'
    ])
    ->latest()
    ->paginate(10);

    return response()->json($transactions);
}
public function exportCsv()
{
    return Excel::download(
        new TransactionsExport,
        'transactions.csv'
    );
}
}
