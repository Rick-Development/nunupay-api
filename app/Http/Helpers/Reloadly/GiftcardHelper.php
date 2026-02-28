<?php
namespace App\Http\Helpers\Reloadly;

use App\Http\Helpers\GiftCardConnectionHelper;

class GiftcardHelper extends GiftCardConnectionHelper{
    
    public function getCategories(){
        $url = '/product-categories';
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
       public function viewAccountBalance(){
        $url = '/accounts/balance';
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
       public function getCountries(){
        $url = '/countries';
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
       public function getCountryByISOCode($countrycode){
        $url = "/countries/$countrycode";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
       public function getProducts($data){
        // $countryCode = '', $size = 10, $productCategoryId = '', $productName = '', $page = '', $includeRange = true, $includeFixed = ''
        $countryCode = $data['countryCode'] ?? '';
        $size = $data['size'] ?? 10;    
        $productCategoryId = $data['productCategoryId'] ?? '';
        $productName = $data['productName'] ?? '';
        $page = $data['page'] ?? '';
        $includeRange = $data['includeRange'] ?? '';
        $includeFixed = $data['includeFixed'] ?? '';

        $url = "/products?size=$size&countryCode=$countryCode&productCategoryId=$productCategoryId&productName=$productName&includeRange=$includeRange&includeFixed=$includeFixed&page=$page";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
       public function getProductByID($productid){
        $url = "/products/$productid";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
       public function getProductByISOCode($countrycode){
        $url = "/countries/$countrycode/products";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
       public function getRedeemInstructions(){
        $url = "/redeem-instructions";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
    
       public function getRedeemInstructionsByProductId($productId){
        $url = "/products/$productId/redeem-instructions";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
    
       public function fetchFXRate($currencyCode,$amount){
        $url = "/fx-rate?currencyCode=$currencyCode&amount=$amount";
    
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
     public function getDiscounts(){
        $url = "/discounts";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
     public function getDiscountByProductId($productId){
        $url = "/products/$productId/discounts";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
     public function getTransactions(){
        $url = "/reports/transactions";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
    
     public function getTransactionById($transactionId){
        $url = "/reports/transactions/$transactionId";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
    
     public function getRedeemCode($transactionId){
        $url = "/orders/transactions/$transactionId/cards";
        $response = $this->get($url);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }
    
    
    
     public function  orderGiftCard($data){
        $url = "/orders";
        $response = $this->post($url,$data);
        $decodedResponse = json_decode($response,true);
        return $decodedResponse;
    }


       
}