<?php

namespace App\Exports;

use App\Models\StockTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return StockTransaction::with([
                'product',
                'supplier'
            ])
            ->get()
            ->map(function ($transaction) {

                return [

                    'ID' => $transaction->id,

                    'Product' => $transaction->product?->name,

                    'Supplier' => $transaction->supplier?->name,

                    'Type' => strtoupper($transaction->type),

                    'Quantity' => $transaction->quantity,

                    'Date' => $transaction->created_at->format('Y-m-d H:i:s'),

                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product',
            'Supplier',
            'Type',
            'Quantity',
            'Date',
        ];
    }
}