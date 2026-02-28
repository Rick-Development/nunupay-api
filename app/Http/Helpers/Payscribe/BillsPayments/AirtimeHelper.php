<?php

namespace App\Http\Helpers\Payscribe\BillsPayments;

use App\Http\Helpers\ConnectionHelper;


class AirtimeHelper extends ConnectionHelper {
    public function __construct(){
        parent::__construct();
    }

    public function vendairtime($data){
        $url = "/airtime";
        // $data = [
        //     "network" => "mtn",
        //     "amount" => 50,
        //     "recipient" => "08169254598",
        //     "ported" => false,
        //     "ref" => "my-system-transaction-id"
        // ];

        return $this->post($url,$data);

    }
}