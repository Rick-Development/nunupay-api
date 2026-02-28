<?php

namespace App\Http\Helpers\Payscribe\BillsPayments;

use App\Http\Helpers\ConnectionHelper;
use App\Http\Helpers\WiseConnectionHelper;
use Illuminate\Support\Facades\Http;

class TransferFlowHelper extends WiseConnectionHelper {
    public function __construct(){
        parent::__construct();
    }

    public function profileList(){
        $url = "/v1/profiles";
        return $this->get($url);
    }

    public function createQuote($data){
        $url = "/v2/quotes";
        return $this->post($url,$data);
    }

    public function existingRecipient($data){
        $url = "/v1/accounts/?currency=$data[currency]";
        return $this->get($url);
    }

    public function createRecipient($data){
        $url = "/v1/quotes/$data[qouteId]/account-requirements";
        return $this->get($url);
    }

    public function updateForm($data, $newquoteId){
        $url = "/v1/quotes/$newquoteId/account-requirements";
        return $this->post($url,$data);
    }

    public function createAccount($data){
        $url = "/v1/accounts";
        return $this->post($url,$data);
    }

    public function generateGUID($data){
        $data = Http::get('https://www.uuidgenerator.net/api/guid')->json();
        return $data;
    }

    public function getTransferExtra($data){
        $url = "/v1/transfer-requirements";
        return $this->post($url,$data);
    }

    public function updateTransferExtra($data){
        $url = "/v1/transfer-requirements";
        return $this->post($url,$data);
    }

    public function createTransfer($data){
        $url = "/v1/transfers";
        return $this->post($url,$data);
    }

    public function fundTransfer($data, $transferId){
        $url = "/v3/profiles/<profile-on-behalf-of-which-transfer-was-created>/transfers/{{$transferId}}/payments";
        return $this->post($url, $data);
    }
}