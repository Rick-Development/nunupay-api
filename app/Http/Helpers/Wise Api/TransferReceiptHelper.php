<?php

namespace App\Http\Helpers\Payscribe\BillsPayments;

use App\Http\Helpers\ConnectionHelper;
use App\Http\Helpers\WiseConnectionHelper;
use Illuminate\Support\Facades\Http;

class TransferReceiptHelper extends WiseConnectionHelper {
    public function __construct(){
        parent::__construct();
    }

    public function getReciept($data){
        $url = "/v1/transfers/{{$data}}/receipt.pdf";
        return $this->get($url);
    }
}