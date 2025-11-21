<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\CardTransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PayscribeVirtualCardTransaction;


class PayscribeCardTransactionController extends Controller
{
    private $currentDate;

    public function __construct(private CardTransactionHelper $cardTransactionHelper){
        $this->currentDate = date('Y-m-d'); // Current date

    }

    public function createCardTransaction(Request $request)
{
    $data = $request->validate([
        'card_id'     => 'required|string',
        'start_date'  => 'sometimes|date',
        'end_date'    => 'sometimes|date',
        'page'        => 'sometimes|integer',
    ]);

    // Generate reference
    $data['ref'] = (string) Str::uuid();

    // Default values
    $cardId     = $data['card_id'];
    $startDate  = $data['start_date'] ?? '2025-11-01';
    $endDate    = $data['end_date'] ?? now()->toDateString();   // current date
    $page       = $data['page'] ?? 1;

    // Eloquent Query with filters
    $transactions = PayscribeVirtualCardTransaction::query()
        ->where('user_id', auth()->id())
        ->where('card_id', $cardId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->latest()
        ->paginate(10, ['*'], 'page', $page); // optional pagination

    return response()->json([
        // 'ref'          => $data['ref'],
        // 'filters_used' => compact('cardId', 'startDate', 'endDate', 'page'),
        'transactions' => $transactions,
    ], 200);
}


}