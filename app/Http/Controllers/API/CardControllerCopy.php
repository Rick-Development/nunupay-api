<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CardController extends Controller
{
    protected $baseUrl = 'https://sandbox.payscribe.ng/api/v1/cards'; // Base URL for card-related API

    public function getCardTransactions($cardId, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'page_size' => 'integer|nullable',
            'page' => 'integer|nullable',
        ]);

        $url = "{$this->baseUrl}/{$cardId}/transactions?" . http_build_query($request->all());

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ps_live_b9a258625363b2a3863e45053da267134152cd5606029bcfe1a39e71e1f72c3c',
        ])->get($url);

        return $response->json();
    }

    public function freezeCard($cardId)
    {
        $url = "{$this->baseUrl}/{$cardId}/freeze";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ps_live_b9a258625363b2a3863e45053da267134152cd5606029bcfe1a39e71e1f72c3c',
        ])->patch($url);

        return $response->json();
    }

    public function unfreezeCard($cardId)
    {
        $url = "{$this->baseUrl}/{$cardId}/unfreeze";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ps_live_b9a258625363b2a3863e45053da267134152cd5606029bcfe1a39e71e1f72c3c',
        ])->patch($url);

        return $response->json();
    }

    public function terminateCard($cardId)
    {
        $url = "{$this->baseUrl}/{$cardId}/terminate";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ps_live_b9a258625363b2a3863e45053da267134152cd5606029bcfe1a39e71e1f72c3c',
        ])->patch($url);

        return $response->json();
    }
}