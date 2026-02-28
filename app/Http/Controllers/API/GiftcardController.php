<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Http\Helpers\Reloadly\GiftcardHelper;
use App\Models\GiftCardDetail;
use Illuminate\Support\Facades\Storage;

class GiftcardController extends Controller
{
    //
    private $giftcardHelper;
    public function __construct()
    {
        $this->giftcardHelper  = new GiftcardHelper();
    }

    public function getCategories()
    {
        $response = $this->giftcardHelper->getCategories();
        return response()->json($response);
    }

    public function veiwAccountBalance() {
        $response = $this->giftcardHelper->viewAccountBalance();
        return response()->json($response);
    }
    
    
    public function getCountries(){
        return response()->json($this->giftcardHelper->getCountries());
    }

    public function getCountriesByISOCode(Request $request) {
        $data = $request->validate([
            'countryCode' => 'required | string'
        ]);
        $response = $this->giftcardHelper->getCountryByISOCode($data['countryCode']);
        return response()->json($response);
    }

    public function getProducts(Request $request){
        $data = $request->validate([
            'size' => 'sometimes',
            'page' => 'sometimes',
            'productName' => 'sometimes',
            'countryCode' => 'sometimes',
            'productCategoryId' => 'sometimes',
            'includeRange' => 'sometimes | boolean',
            'includeFixed' => 'sometimes | boolean'
        ]);
        // 'products?size=10&page=1&productName=Amazon&countryCode=US&productCategoryId=2&includeRange=true&includeFixed=true' \

        $response = $this->giftcardHelper->getProducts($data);
        return response()->json($response);
    }

    public function getProductByID(string $id){
        // $data = $request->validate([
        //     'productid' => 'required | string'
        // ]);
        $response = $this->giftcardHelper->getProductByID($id);
        return response()->json($response);
    }

    public function getProductByISOCode(Request $request){
        $data = $request->validate([
            'countryCode' => 'required | string'
        ]);
        $response = $this->giftcardHelper->getProductByISOCode($data['countryCode']);
        return response()->json($response);
    }

    public function getRedeemInstructions(Request $request){
        // $data = $request->validate([
        //     'productid' => 'required | string'
        // ]);
        $response = $this->giftcardHelper->getRedeemInstructions();
        return response()->json($response);
    }

    public function getRedeemInstructionsByProductId(string $id){
        // $data = $request->validate([
        //     'productid' => 'required | string'
        // ]);
        $response = $this->giftcardHelper->getRedeemInstructionsByProductId($id);
        return response()->json($response);
    }

    public function fetchFXRate(Request $request){
        $data = $request->validate([
            'currencyCode' => 'required | string',
            'amount' => 'required | string'
        ]);
        $response = $this->giftcardHelper->fetchFXRate($data['currencyCode'], $data['amount']);
        return response()->json($response);
    }

    public function getDiscounts(){
        $response = $this->giftcardHelper->getDiscounts();
        return response()->json($response);
    }

    public function getDiscountsByProductId(string $id){
        // $data = $request->validate([
        //     'productid' => 'required | string'
        // ]);
        $response = $this->giftcardHelper->getDiscountByProductId($id);
        return response()->json($response);
    }
    
    public function getTransactions() {
        $response = $this->giftcardHelper->getTransactions();
        return response()->json($response);
    }

    public function getTransactionById(string $id) {
        // $data = $request->validate([
        //     'transactionId' => 'required | string'
        // ]);
        $response = $this->giftcardHelper->getTransactionById($id);
        return response()->json($response);
    }
    
    public function getRedeemCode(Request $request){
        $data = $request->validate([
            'transaction_id' => 'required | integer'
        ]);
        $response = $this->giftcardHelper->getRedeemCode($data['transaction_id']);
        return response()->json($response);
    }
    public function orderGiftCard(Request $request){
        
        return response()->json($this->giftcardHelper->orderGiftCard($request->all()));
    }

    public function recieveGiftCard(Request $request) {
        $data = $request->validate([
            'productName' => 'required | string',
            'countryCode' => 'required | string',
            'currencyCode' => 'required | string',
            'giftcardBrand' => 'required | string',
            'images' => 'required',
            'images.*' => 'required | image',
            'giftcardCode' => 'required | string',
        ]);
        if($request->file('images'))
        $uploadImg = [];
        foreach($request->file('images') as $image){

            $uploadImg[] = Storage::disk('public')->put('giftcards', $image);
            // $uploadImg[] = $image->store('uploads', 'public');

        }
        $sellerMail = auth()->user()->email;
        
        $gifcardDetails = GiftCardDetail::create([
            'product_name' => $data['productName'],
            'country_code' => $data['countryCode'],
            'currency_code' => $data['currencyCode'],
            'giftcard_brand' => $data['giftcardBrand'],
            'seller_email' => $sellerMail,
            'images' => $uploadImg,
            'trade' => 'pending',
            'giftcardCode' => $data['giftcardCode'],
        ]);

        return response()->json([
            "data"  => $gifcardDetails,
        ]);
       
    }

    public function getCustomerGiftCard() {
        $response = GiftCardDetail::paginate(10);
        return response()->json(['data' => $response], 200);
    }

    public function UpdateGiftCardTrade(Request $request, string $id) {
        $message = [ 
            'trade' => 'The trade field is invalid.',
            'trade.in' => 'Invalid trade status. Must be pending, success, or inprocess',
        ];
        $data = $request->validate([
            'trade' => 'required | string | in:pending,success,inprocess',
            'message' => 'required | string'
        ], $message);

        try {

            $giftCardDetail = GiftCardDetail::findOrFail($id);
            $giftCardDetail->update([
                    'trade' => $data['trade'],
                    'message' => $data['message'],
                ]);
        
            // return response()->json(['message' => "success"], 200);
            return response()->json($giftCardDetail, 200);
        }catch(Exception $esc) {
            return response()->json(['error' => "Invaild Id"]);
        }
        

    }


    
    // public function getRedeemCode(Request $request){
    //     return response()->json($this->giftcardHelper->getRedeemCode($request->transaction_id));
    // }
    
    
        
    
}