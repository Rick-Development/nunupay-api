<?php

use App\Http\Controllers\API\PayscribeCreateCardController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\OTPController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VirtualCardController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\RecipientController;
use App\Http\Controllers\API\MoneyTransferController;
use App\Http\Controllers\API\SupportTicketController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\DepositController;
use App\Http\Controllers\API\TwoFASecurityController;
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\CardController;
use App\Http\Controllers\API\NinePaymentService;
// use App\Http\Controllers\API\BillpaymentController;
use App\Http\Controllers\API\TopUpController;
use App\Http\Controllers\API\BillPaymentController;
use App\Http\Controllers\API\WalletControllerCopy;
use App\Http\Controllers\API\PayscribeUserCardController;
use App\Http\Controllers\API\PayscribeCustomerController;
use App\Http\Controllers\API\AdController;
use App\Http\Controllers\API\BankListController;
use App\Http\Controllers\API\NinepsbWebhookController;
use App\Http\Controllers\API\NinePSBNotificationController;
use App\Http\Controllers\API\GiftcardController;
use App\Http\Controllers\API\KycLookupController;
use App\Http\Controllers\API\PayscribeAirtimeController;
use App\Http\Controllers\API\PayscribeAirtimeToWalletController;
use App\Http\Controllers\API\PayscribeCableTvSubsController;
use App\Http\Controllers\API\PayscribeCardDetailsController;
use App\Http\Controllers\API\PayscribeCardTransactionController;
use App\Http\Controllers\API\PayscribeController;
use App\Http\Controllers\API\PayscribeDataBundleController;
use App\Http\Controllers\API\PayscribeElectricityBillsController;
use App\Http\Controllers\API\PayscribeEpinsController;
use App\Http\Controllers\API\PayscribeFreezeCardController;
use App\Http\Controllers\API\PayscribeFundBetWalletController;
use App\Http\Controllers\API\PayscribeInternetSubController;
use App\Http\Controllers\API\PayscribelIntAirtimeDataController;
use App\Http\Controllers\API\PayscribeNGNVirtualAccountController;
use App\Http\Controllers\API\PayscribePayoutController;
use App\Http\Controllers\API\PayscribeTerminateCardController;
use App\Http\Controllers\API\PayscribeTopupCardController;
use App\Http\Controllers\API\PayscribeUnfreezeCardController;
use App\Http\Controllers\API\PayscribeWithdrawFromCardController;
use App\Http\Controllers\PayscribeWebhookController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// routes/api.php

// routes/api.php


/*=== Test  ===*/
Route::get('/test', function(){
    return response()->json([
        'message' => "gain api working"
    ]);
}); 
///Email verification
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

 
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
 
    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

 
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
 
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/ninepsbwebhook', [NinepsbWebhookController::class, 'handle']);

Route::post('/ninepsbnotification', [NinePSBNotificationController::class, 'sendNotification']);
Route::post('/ninepsbpaymentnotification', [NinePSBNotificationController::class, 'sendPaymentNotification']);




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('9sp', [NinePaymentService::class, 'index'])->name('sp');

Route::post('virtual-card/ufitpay/callback', [VirtualCardController::class, 'ufitpayCallBack'])->name('ufitpay.Callback');
Route::post('virtual-card/flutterwave/callback', [VirtualCardController::class, 'flutterwavedCallBack'])->name('flutterwave.Callback');
Route::post('payout/{code}', [VirtualCardController::class, 'payout'])->name('payout');
Route::get('/ads', [AdController::class, 'index']);


/*=== API For Application ===*/
Route::get('app-config', [HomeController::class, 'appConfig']);
Route::get('language/{id?}', [HomeController::class, 'language']);

/*-- User Authentication --*/
Route::post('register', [UserAuthController::class, 'register']);
Route::post('login', [UserAuthController::class, 'login']);
Route::post('login-with-pin', [UserAuthController::class, 'loginWithPin']);
Route::post('recovery-pass/get-email', [UserAuthController::class, 'getEmailForRecoverPass']);
Route::post('recovery-pass/get-code', [UserAuthController::class, 'getCodeForRecoverPass']);
Route::post('update-pass', [UserAuthController::class, 'updatePass']);
Route::post('logout', [UserAuthController::class, 'logout'])
    ->middleware('auth:sanctum');
// Route::post('upate-profile', );

Route::middleware('auth:sanctum')->group(function () {
    
        

    /*--Verification--*/
    Route::post('twoFA-Verify', [VerificationController::class, 'twoFAverify']);
    Route::post('mail-verify', [VerificationController::class, 'mailVerify']);
    Route::post('sms-verify', [VerificationController::class, 'smsVerify']);
    Route::get('resend-code', [VerificationController::class, 'resendCode']);

    Route::group(['middleware' => ['CheckVerificationApi']], function () {
        Route::get('transaction-list', [HomeController::class, 'transactionList']);
        Route::get('fund-list', [HomeController::class, 'fundList']);
        Route::get('referral-list', [HomeController::class, 'referList']);
        Route::get('referral-details', [HomeController::class, 'referList']);
        Route::get('gateways', [HomeController::class, 'gateways']);
        Route::get('notification-settings', [HomeController::class, 'notificationSettings']);
        Route::post('notification-permission', [HomeController::class, 'notificationPermissionStore']);
        Route::get('pusher-config', [HomeController::class, 'pusherConfig']);

        Route::get('dashboard', [HomeController::class, 'dashboard']);

        /*wallet*/
        Route::get('wallet-list', [WalletController::class, 'walletList']);
        Route::post('wallet-store', [WalletController::class, 'store']);
        Route::post('wallet-exchange', [WalletController::class, 'walletExchange']);
        Route::post('money-exchange', [WalletController::class, 'moneyExchange']);
        Route::get('wallet-transaction/{uuid}', [WalletController::class, 'walletTransaction']);
        Route::post('default-wallet/{id}', [WalletController::class, 'defaultWallet']);


        /*--profile--*/
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::get('/pin', [ProfileController::class, 'getPin']);
        Route::post('/update-pin', [ProfileController::class, 'updatePin']);
        Route::post('profile-update/image', [ProfileController::class, 'profileUpdateImage']);
        Route::post('profile-update', [ProfileController::class, 'profileUpdate']);
        Route::put('email-update/{user}', [ProfileController::class, 'updateEmail']);
        Route::post('update-password', [ProfileController::class, 'updatePassword']);
        Route::get('verify/{id?}', [ProfileController::class, 'verify']);
        Route::post('kyc-submit', [ProfileController::class, 'kycVerificationSubmit']);

        Route::post('/delete-account', [ProfileController::class, 'deleteAccount']);
        Route::post('/logout-from-all-devices', [HomeController::class, 'logoutFromAllDevices'])
            ->middleware(['auth', 'web']);

        /*--2FA Security--*/
        Route::get('2FA-security', [TwoFASecurityController::class, 'twoFASecurity']);
        Route::post('2FA-security/enable', [TwoFASecurityController::class, 'twoFASecurityEnable']);
        Route::post('2FA-security/disable', [TwoFASecurityController::class, 'twoFASecurityDisable']);

        /*--ticket--*/
        Route::get('ticket-list', [SupportTicketController::class, 'ticketList']);
        Route::get('ticket-view/{ticketId}', [SupportTicketController::class, 'ticketView']);
        Route::post('create-ticket', [SupportTicketController::class, 'createTicket']);
        Route::post('reply-ticket/{id}', [SupportTicketController::class, 'replyTicket']);
        Route::patch('close-ticket/{id}', [SupportTicketController::class, 'closeTicket']);
        Route::delete('delete-ticket/{ticketId}', [SupportTicketController::class, 'deleteTicket']);

        /*--recipient--*/
        Route::get('recipient-list', [RecipientController::class, 'recipientList']);
        Route::get('recipient-details/{uuid}', [RecipientController::class, 'recipientDetails']);
        Route::post('recipient-store', [RecipientController::class, 'store']);
        Route::post('recipient-user-store', [RecipientController::class, 'userStore']);
        Route::put('recipient-update-name/{recipient}', [RecipientController::class, 'updateName']);
        Route::delete('recipient-delete/{recipient}', [RecipientController::class, 'destroy']);

        Route::get('get-services', [RecipientController::class, 'getServices'])->name('getServices');
        Route::get('get-bank', [RecipientController::class, 'getBank'])->name('getBank');
        Route::get('generate-fields', [RecipientController::class, 'generateFields'])->name('generateFields');

        /* ===== Money Request ===== */
        Route::middleware('ApiKYC')->group(function () {
            Route::get('money-request-form/{uuid}', [MoneyRequestController::class, 'showRequestMoneyForm']);
            Route::post('money-request', [MoneyRequestController::class, 'requestMoney']);
            Route::post('money-request-action', [MoneyRequestController::class, 'moneyRequestAction'])->name('moneyRequestAction');
        });
        Route::get('money-request-list', [MoneyRequestController::class, 'moneyRequestList'])->name('moneyRequestList');
        Route::get('money-request-details/{trx_id}', [MoneyRequestController::class, 'moneyRequestDetails'])->name('moneyRequestDetails');

        /*--money transfer--*/
        Route::get('transfer-list', [MoneyTransferController::class, 'transferList']);
        Route::get('transfer-details/{uuid}', [MoneyTransferController::class, 'transferDetails']);
        Route::get('transfer-amount', [MoneyTransferController::class, 'transferAmount']);
        Route::match(['get', 'post'], '/transfer-recipient/{country?}', [MoneyTransferController::class, 'transferRecipient']);

        Route::middleware('ApiKYC')->group(function () {
            Route::get("transfer-review/{uuid}", [MoneyTransferController::class, 'transferReview']);
            Route::post("transfer-payment-store", [MoneyTransferController::class, 'paymentStore']);
        });

        Route::get("transfer-pay/{uuid}", [MoneyTransferController::class, 'transferPay']);
        Route::post("money-transfer-post", [MoneyTransferController::class, 'transferPayment']);
        Route::post("currency-rate", [MoneyTransferController::class, 'currencyRate']);

        Route::match(['get', 'post'], "transfer-otp", [OTPController::class, 'transferOtp']);

        /*--Payment--*/
        Route::get('supported-currency', [DepositController::class, 'supportedCurrency']);
        Route::get('deposit-check-amount', [DepositController::class, 'checkAmount']);
        Route::post('payment-request/{transfer?}', [DepositController::class, 'paymentRequest']);
        Route::get('payment-process/{trx_id}', [PaymentController::class, 'depositConfirm']);
        Route::post('addFundConfirm/{trx_id}', [PaymentController::class, 'fromSubmit']);

        Route::post('card-payment', [PaymentController::class, 'cardPayment']);
        Route::post('payment-done', [PaymentController::class, 'paymentDone']);
        Route::get('payment-webview', [PaymentController::class, 'paymentWebview']);

        /* Virtual Card */
        Route::get('virtual-cards', [CardController::class, 'index']);
        Route::get('virtual-card/order', [CardController::class, 'order']);
        Route::post('virtual-card/order/submit', [CardController::class, 'orderSubmit']);
        Route::match(['get', 'post'], 'virtual-card/confirm/{utr}', [CardController::class, 'confirmOrder']);
        Route::any('virtual-card/order/re-submit', [CardController::class, 'orderReSubmit']);

        Route::post('virtual-card/block/{id}', [CardController::class, 'cardBlock']);
        Route::get('virtual-card/transaction/{id?}', [CardController::class, 'cardTransaction']);


    });



// Top-up routes
Route::prefix('topup')->group(function () {
    Route::get('/network', [TopUpController::class, 'getNetwork']);
    Route::get('/dataPlans', [TopUpController::class, 'getDataPlans']);
    Route::post('/airtime', [TopUpController::class, 'airtimeTopup']);
    Route::post('/data', [TopUpController::class, 'dataTopup']);
    Route::get('/status', [TopUpController::class, 'getTopupStatus']);
});

// Bill payment routes
Route::prefix('billspayment')->group(function () {
    Route::get('/categories', [BillPaymentController::class, 'getCategories']);
    Route::get('/billers/{categoryId}', [BillPaymentController::class, 'getBillers']);
    Route::get('/fields/{billerId}', [BillPaymentController::class, 'getBillerInputFields']);
    Route::post('/validate', [BillPaymentController::class, 'validateBillerInput']);
    Route::post('/pay', [BillPaymentController::class, 'initiateBillPayment']);
});




         /*==== Gift Card ===*/
    Route::prefix('gift-card')->group(function () {

        Route::get('get-categories', [GiftcardController::class, 'getCategories'])->name('gift.categories');
        
        Route::get('view-account-balance', [GiftcardController::class, 'veiwAccountBalance'])->name('view.account.balance');
        
        Route::get('get-countries', [GiftcardController::class, 'getCountries']);
        
        Route::post('get-countries-byISOcode', [GiftcardController::class, 'getCountriesByISOCode']);

        Route::post('products', [GiftcardController::class, 'getProducts'])->name('gift.products');

        Route::get('products/{id}', [GiftcardController::class, 'getProductByID']);

        Route::post('products-byISOcode', [GiftcardController::class, 'getProductByISOCode']);

        Route::get('get-redeeminstructions', [GiftcardController::class, 'getRedeemInstructions']);

        Route::get('redeeminstruction/{id}', [GiftcardController::class, 'getRedeemInstructionsByProductId']);
        
        Route::post('fx-rate', [GiftcardController::class, 'fetchFXRate']);

        Route::get('discounts', [GiftcardController::class, 'getDiscounts']);

        Route::get('discounts/{id}', [GiftcardController::class, 'getDiscountsByProductId']);

        Route::get('transactions', [GiftcardController::class, 'getTransactions']);

        Route::get('transactions/{id}', [GiftcardController::class, 'getTransactionById']);
          
        // Route::post('redeem-code', [GiftcardController::class, 'getRedeemCode']);


        Route::post('order', [GiftcardController::class, 'orderGiftCard'])->name('gift.order');

        Route::post('redeem-code', [GiftcardController::class, 'getRedeemCode'])->name('gift.redeem.code');

        Route::post('send-gift-card', [GiftcardController::class, 'recieveGiftCard']);

        Route::get('customers-gift-card', [GiftcardController::class, 'getCustomerGiftCard']);
        
        Route::post('customers-gift-card/{id}', [GiftcardController::class, 'UpdateGiftCardTrade']);

          
          
        });
        
        

// Default route for testing
Route::get('/', function() {
    return response()->json(['message' => 'API is working'], 200);
});

});


/*=== API Payscribe ===*/

Route::middleware('auth:sanctum')->group(function () { 
    Route::prefix('payscribe')->group(function () {
        //Fetch Services
        Route::get('fetch-services', [PayscribeController::class, 'fetchServices']);
        
        //Electricity
        Route::post('validate-electricity', [PayscribeElectricityBillsController::class, 'validateElectricity']);
        Route::post('pay-electricity', [PayscribeElectricityBillsController::class, 'payElectricity']);
        Route::post('requery-trans', [PayscribeElectricityBillsController::class, 'requeryTransaction']);
    
        //Airtime
        Route::post('airtime', [PayscribeAirtimeController::class, 'airtime']);
    
        //Cable Tv
        Route::post('fetch-bouquents', [PayscribeCableTvSubsController::class, 'fetchBouquents']);
    
        Route::post('validate-smartcardnumber', [PayscribeCableTvSubsController::class, 'validateSmartCardNumber']);
    
        Route::post('pay-cabletv', [PayscribeCableTvSubsController::class, 'payCableTv']);
    
        Route::post('topup-cabletv', [PayscribeCableTvSubsController::class, 'topUpCableTv']);
    
        //Data Bundle
        Route::post('data-lookup', [PayscribeDataBundleController::class, 'dataLookup']);
        
        Route::post('data-vending', [PayscribeDataBundleController::class, 'dataVending']);
    
        //Internet Subscription
        Route::get('internet-services', [PayscribeInternetSubController::class, 'internetServices']);
    
        Route::get('spectranet-pin-plans', [PayscribeInternetSubController::class, 'spectranetPinPlans']);
    
        Route::post('purchase-spectranet-plans', [PayscribeInternetSubController::class, 'purchaseSpectranetPlans']);
    
        // Route::post('validate-internet-subscription', [PayscribeInternetSubController::class, 'validateInternetSubsription']);
    
        // Route::post('internet-subscription-bundles', [PayscribeInternetSubController::class, 'internetSubsriptionBundles']);
    
        // Route::post('pay-internet-subscription', [PayscribeInternetSubController::class, 'payInternetSubsription']);
    
        //Fund Bet Wallet
        Route::get('betting-service-provider-list', [PayscribeFundBetWalletController::class, 'bettingServiceProviderList']);
    
        Route::post('validate-bet-account', [PayscribeFundBetWalletController::class, 'validateBetAccount']);
    
    
        Route::post('fund-bet-wallet', [PayscribeFundBetWalletController::class, 'fundWallet']);
    
        //KYC 
        Route::post('kyc-lookup', [KycLookupController::class, 'bvnLookup']);

        Route::get('send-kyc-otp', [KycLookupController::class, 'sendKYCOtp']);

        Route::post('verify-kyc-otp', [KycLookupController::class, 'verifyTermiiOtp']);



        //Card Issuing
        Route::post('create-card', [PayscribeCreateCardController::class, 'createCard']);
    
        Route::post('topup-card', [PayscribeTopupCardController::class, 'topupCard']);
    
        Route::post('withdraw-from-card', [PayscribeWithdrawFromCardController::class, 'withdraw']);
        
        Route::post('card-details', [PayscribeCardDetailsController::class, 'getCardDetails']);
        
        Route::post('card-transaction', [PayscribeCardTransactionController::class, 'createCardTransaction']);
    
        Route::post('freeze-card', [PayscribeFreezeCardController::class, 'freezeCard']);
    
        Route::post('unfreeze-card', [PayscribeUnfreezeCardController::class, 'unfreezeCard']);
    
        Route::post('terminate-card', [PayscribeTerminateCardController::class, 'terminateCard']);
    
        Route::get('get-bank-list', [BankListController::class, 'getBankList']);




        //Customer
        
        Route::post('upgrade-to-tier-one', [PayscribeCustomerController::class, 'upgradeToTierOne']);

        Route::post('upgrade-to-tier-two', [PayscribeCustomerController::class, 'upgradeToTierTwo']);

        Route::get('get-customers', [PayscribeCustomerController::class, 'getAllCustomers']);

        Route::post('get-customer-details', [PayscribeCustomerController::class, 'getCustomerDetails']);

        Route::post('toggle-customer-blacklist', [PayscribeCustomerController::class, 'toggleCustomerBlacklist']);

        Route::post('update-customer', [PayscribeCustomerController::class, 'updateCustomer']);

        Route::post('get-customer-transaction', [PayscribeCustomerController::class, 'getCustomerTransactions']);

        // Route::post('customer-transactions', [PayscribeCustomerController::class, 'terminateCard']);

        Route::post('customer-balance', [PayscribeCustomerController::class, 'customerBalance']);

        Route::get('transactions', [PayscribeCustomerController::class, 'customerTransactions']);

        Route::post('reset-pin', [PayscribeCustomerController::class, 'resetPin']);

        //NGNvirtualAccounts

        Route::get('virtual-account-details', [PayscribeNGNVirtualAccountController::class, 'virtualAccountDetails']);

        Route::get('deactivate-virtual-account', [PayscribeNGNVirtualAccountController::class, 'deactivateVirtualAccount']);

        Route::get('activate-virtual-account', [PayscribeNGNVirtualAccountController::class, 'activateVirtualAccount']);

        Route::post('create-temporary-virtual-account', [PayscribeNGNVirtualAccountController::class, 'dynamicTemporaryVirtualAccount']);
    

        //Payout
        Route::post('account-lookup', [PayscribePayoutController::class, 'accountLookUp']);

        Route::post('payout-fee', [PayscribePayoutController::class, 'payoutFee']);

        Route::post('transfer', [PayscribePayoutController::class, 'transfer']);

        Route::post('verify-transfer', [PayscribePayoutController::class, 'verifyTransfer']);

        //AirtimeToWallet
        Route::get('airtime-to-wallet-lookup', [PayscribeAirtimeToWalletController::class, 'airtimeToWalletLookup']);

        Route::post('airtime-to-wallet', [PayscribeAirtimeToWalletController::class, 'airtimeToWallet']);

        //E-pins
        Route::get('avaliable-epins', [PayscribeEpinsController::class, 'avaliableEpin']);

        Route::post('purchase-epin', [PayscribeEpinsController::class, 'purchaseEpin']);
        
        Route::post('jamb-user-lookup', [PayscribeEpinsController::class, 'jambUserLookup']);
        
        Route::post('retreive-epin', [PayscribeEpinsController::class, 'retreiveEpin']);

        //IntAirtimeData
        Route::get('int-bill-countries', [PayscribelIntAirtimeDataController::class, 'IntBillsCountries']);

        Route::post('int-bill-providers', [PayscribelIntAirtimeDataController::class, 'IntBillsProviders']);

        Route::post('int-bill-products', [PayscribelIntAirtimeDataController::class, 'IntBillsProducts']);

        
        Route::post('estimate-rates', [PayscribelIntAirtimeDataController::class, 'EstimateRates']);

        Route::post('vend-int-bills', [PayscribelIntAirtimeDataController::class, 'VendIntBills']);

        //Webhook url
    
        Route::post('payscribe-webhook', [PayscribeWebhookController::class, 'handle'])->withoutMiddleware(['auth:sanctum']);

        /// Card Rate
        Route::get('card-issuing-rate', [PayscribeCreateCardController::class, 'cardIssuingRate']);

        Route::get('card-deposit-rate', [PayscribeCreateCardController::class, 'cardDepositRate']);
        
        Route::get('card-withdrawal-rate', [PayscribeCreateCardController::class, 'cardWithdrawalRate']);

        Route::get('card-details', [PayscribeCreateCardController::class, 'customerCardDetails']);

        Route::get('card-transactions/{id}', [PayscribeCreateCardController::class, 'customerTransactions']);
    });

});