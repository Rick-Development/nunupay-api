<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankList;
use Illuminate\Http\Request;

class BankListController extends Controller
{
    public function getBankList() {
        $data = BankList::all();

        return response()->json([
            "bankList" => $data
        ]);
    }
}