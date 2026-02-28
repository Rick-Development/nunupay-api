<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController as UserLoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\InAppNotificationController;
use App\Http\Controllers\khaltiPaymentController;
use App\Http\Controllers\ManualRecaptchaController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\MoneyTransferController;
use App\Http\Controllers\User\RecipientController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\VerificationController;
use App\Http\Controllers\User\VirtualCardController;
use App\Http\Controllers\API\PaymentController as ApiPayment;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\SocialiteController;
use App\Http\Controllers\OTPController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\GiftcardWebhookController;
use App\Http\Controllers\PayscribeWebhookController;



Route::get('/{any}', function () {
      return redirect('/index.html');
})->where('any', '.*');

$basicControl = basicControl();
//   Route::get('/', function(){
//         return redirect('/index.html');
//     }); 


Route::post('/webhook/giftcard', [GiftcardWebhookController::class, 'handle']);
Route::post('/webhook/payscribe', [PayscribeWebhookController::class, 'handle']);


Route::get('payment/view/{deposit_id}', [ApiPayment::class, 'paymentView'])->name('paymentView');

Route::get('/clear', function () {
    // auth()->guard('admin')->loginUsingId(1);
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('optimize:clear', array(), $output);
    return $output->fetch();
})->name('/clear');

Route::get('queue-work', function () {
    return Artisan::call('queue:work', ['--stop-when-empty' => true]);
})->name('queue.work');



Route::get('/key', function(){
     artisan::call('key:generate');
});

Route::get('maintenance-mode', function () {
    if (!basicControl()->is_maintenance_mode) {
        return redirect(route('page'));
    }
    $data['maintenanceMode'] = \App\Models\MaintenanceMode::first();
    return view(template() . 'maintenance', $data);
})->name('maintenance');


Route::get('gate-rate', function () {
    return Artisan::call('app:currency-rate-update');
})->name('update.rate');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.update');

Route::view('instruction/page', 'instruction-page')->name('instructionPage');


Route::group(['middleware' => ['maintenanceMode']], function () use ($basicControl) {

    Route::group(['middleware' => ['guest']], function () {
        // Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
        Route::get('/login' , function(){
        return redirect('/index.html');
    })->name('login');
        
        Route::post('/login', [UserLoginController::class, 'login'])->name('login.submit');
    });

    Route::match(['get', 'post'], 'user-otp', [VerificationController::class, 'userOtp'])->name('userOtp');

    Route::group(['middleware' => ['auth'], 'prefix' => 'user', 'as' => 'user.'], function () {
        Route::get('check', [VerificationController::class, 'check'])->name('check');
        Route::get('resend_code', [VerificationController::class, 'resendCode'])->name('resendCode');
        Route::post('mail-verify', [VerificationController::class, 'mailVerify'])->name('mailVerify');
        Route::post('sms-verify', [VerificationController::class, 'smsVerify'])->name('smsVerify');
        Route::post('twoFA-Verify', [VerificationController::class, 'twoFAverify'])->name('twoFA-Verify');
        Route::view('otp-options', template() . 'auth.verification.getOtp')->name('otpOptions');

        Route::middleware('userCheck')->group(function () {

            Route::group(['prefix' => 'ticket', 'as' => 'ticket.'], function () {
                Route::get('/', [SupportTicketController::class, 'index'])->name('list');
                Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
                Route::post('/create', [SupportTicketController::class, 'store'])->name('store');
                Route::get('/view/{ticket}', [SupportTicketController::class, 'ticketView'])->name('view');
                Route::put('/reply/{ticket}', [SupportTicketController::class, 'reply'])->name('reply');
                Route::get('/download/{ticket}', [SupportTicketController::class, 'download'])->name('download');
            });

            Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
            Route::post('wallet-store', [WalletController::class, 'store'])->name('wallet.store');
            Route::get('w-exchange/{uuid}', [WalletController::class, 'walletExchange'])->name('wallet.exchange');
            Route::post('convert-money', [WalletController::class, 'moneyExchange'])->name('moneyExchange');
            Route::get('wallet-details/{uuid}', [WalletController::class, 'walletDetails'])->name('wallet.details');
            Route::post('default-wallet/{id}', [WalletController::class, 'defaultWallet'])->name('defaultWallet');
            Route::get('wallet-list', [WalletController::class, 'walletList'])->name('walletList');


            Route::get('settings', [HomeController::class, 'settings'])->name('settings');
            Route::post('settings/notification-permission/store', [HomeController::class, 'notificationPermissionStore'])
                ->name('notification.permission.store');
            Route::get('earn', [HomeController::class, 'earn'])->name('earn');
            Route::get('referral-list', [HomeController::class, 'referList'])->name('referList');
            Route::get('referral-details/{id}', [HomeController::class, 'referDetails'])->name('referDetails');
            Route::get('profile', [HomeController::class, 'profile'])->name('profile');
            Route::post('profile-update', [HomeController::class, 'profileUpdate'])->name('profile.update');
            Route::post('profile-update/image', [HomeController::class, 'profileUpdateImage'])->name('profile.update.image');
            Route::post('update/password', [HomeController::class, 'updatePassword'])->name('updatePassword');
            Route::put('change-email/{user}', [HomeController::class, 'updateEmail'])->name('update.email');
            Route::post('save-token', [HomeController::class, 'saveToken'])->name('save.token');

            Route::get('verify/{id}', [HomeController::class, 'verify'])->name('verify');
            Route::post('kyc/submit', [HomeController::class, 'kycVerificationSubmit'])->name('kyc.verification.submit');
            Route::get('add-fund/', [HomeController::class, 'addFund'])->name('add.fund');
            Route::get('funds', [HomeController::class, 'fund'])->name('fund.index');
            Route::get('all-transaction-list', [HomeController::class, 'allTransaction'])->name('allTransaction');

            Route::get('twostep-security', [HomeController::class, 'twoStepSecurity'])->name('twostep.security');
            Route::post('twoStep-enable', [HomeController::class, 'twoStepEnable'])->name('twoStepEnable');
            Route::post('twoStep-disable', [HomeController::class, 'twoStepDisable'])->name('twoStepDisable');

            Route::get('/delete-account', [HomeController::class, 'deleteAccount'])->name('delete.account');
            Route::post('/logout-from-all-devices', [HomeController::class, 'logoutFromAllDevices'])
                ->middleware(['auth', 'web'])
                ->name('logout.from.all.devices');

            /* ===== Push Notification ===== */
            Route::get('push-notification-show', [InAppNotificationController::class, 'show'])->name('push.notification.show');
            Route::get('push.notification.readAll', [InAppNotificationController::class, 'readAll'])->name('push.notification.readAll');
            Route::get('push-notification-readAt/{id}', [InAppNotificationController::class, 'readAt'])->name('push.notification.readAt');

            /* Recipient Manage */
            Route::get('recipient', [RecipientController::class, 'index'])->name('recipient.index');
            Route::get('recipient-create/{type}/{addNew?}/{countryName?}', [RecipientController::class, 'create'])
                ->name('recipient.create');
            Route::post('recipient-store', [RecipientController::class, 'store'])->name('recipient.store');
            Route::post('recipient-user-store', [RecipientController::class, 'userStore'])->name('recipient.userStore');
            Route::get('recipient-details/{uuid}', [RecipientController::class, 'details'])->name('recipient.details');
            Route::put('recipient/{recipient}', [RecipientController::class, 'updateName'])->name('recipient.update.name');
            Route::delete('recipient/{recipient}', [RecipientController::class, 'destroy'])->name('recipient.destroy');
            //axios request
            Route::get('/get-services', [RecipientController::class, 'getServices'])->name('getServices');
            Route::get('/get-bank', [RecipientController::class, 'getBank'])->name('getBank');
            Route::get('/generate-fields', [RecipientController::class, 'generateFields'])->name('generateFields');

            /* ===== Money Request ===== */
            Route::middleware('kyc')->group(function () {
                Route::get('recipient-request-money/{uuid}', [MoneyRequestController::class, 'showRequestMoneyForm'])
                    ->name('requestMoneyForm');
                Route::post('recipient-request-money', [MoneyRequestController::class, 'requestMoney'])
                    ->name('requestMoney');
                Route::post('money-request-action', [MoneyRequestController::class, 'moneyRequestAction'])
                    ->name('moneyRequestAction');
            });

            Route::get('money-request-list', [MoneyRequestController::class, 'moneyRequestList'])->name('moneyRequestList');
            Route::get('money-request-details/{trx_id}', [MoneyRequestController::class, 'moneyRequestDetails'])->name('moneyRequestDetails');

            /* ===== Money Transfer ===== */
            Route::get('/clear-session', [MoneyTransferController::class, 'clearSession'])->name('clearSession');
            Route::get("/transfer-list", [MoneyTransferController::class, 'transferList'])->name('transferList');
            Route::get("/transfer-details/{uuid}", [MoneyTransferController::class, 'transferDetails'])->name('transferDetails');
            Route::get("/transfer-amount", [MoneyTransferController::class, 'transferAmount'])->name('transferAmount');
            Route::match(['get', 'post'], '/transfer-recipient/{country?}', [MoneyTransferController::class, 'transferRecipient'])->name('transferRecipient');
            Route::post("/transfer-recipient-store", [MoneyTransferController::class, 'storeRecipient'])->name('storeRecipient');
            Route::get('transfer-verification', [MoneyTransferController::class, 'verify'])->name('transfer.verify');
            Route::middleware('kyc')->group(function () {
                Route::get("/transfer-review/{uuid}", [MoneyTransferController::class, 'transferReview'])->name('transferReview');
                Route::post("/payment-store", [MoneyTransferController::class, 'paymentStore'])->name('paymentStore');
                Route::get("/transfer-pay/{uuid}", [MoneyTransferController::class, 'transferPay'])->name('transferPay');
            });
            Route::delete("/transfer-delete/{uuid}", [MoneyTransferController::class, 'destroy'])->name('transferDestroy');
            Route::get("/currency-rate", [MoneyTransferController::class, 'currencyRate'])->name('currencyRate');
            Route::get("/wallet-balance", [MoneyTransferController::class, 'getWalletBalance'])->name('walletBalance');
            Route::match(['get', 'post'], "transfer-otp", [OTPController::class, 'transferOtp'])->name('transferOtp');

            /* Virtual Card */
            Route::get('virtual-card', [VirtualCardController::class, 'index'])->name('virtual.card');
            Route::get('virtual-card/order', [VirtualCardController::class, 'order'])->name('virtual.card.order');
            Route::post('virtual-card/order/submit', [VirtualCardController::class, 'orderSubmit'])->name('virtual.card.orderSubmit');
            Route::match(['get', 'post'], 'virtual-card/confirm/{utr}', [VirtualCardController::class, 'confirmOrder'])->name('order.confirm');
            Route::any('virtual-card/order/re-submit', [VirtualCardController::class, 'orderReSubmit'])->name('virtual.card.orderReSubmit');

            Route::post('virtual-card/block/{id}', [VirtualCardController::class, 'cardBlock'])->name('virtual.cardBlock');
            Route::get('virtual-card/transaction/{id?}', [VirtualCardController::class, 'cardTransaction'])->name('virtual.cardTransaction');

        });
    });

    Route::get('auth/{socialite}', [SocialiteController::class, 'socialiteLogin'])->name('socialiteLogin');
    Route::get('auth/callback/{socialite}', [SocialiteController::class, 'socialiteCallback'])->name('socialiteCallback');

    Route::get('/captcha', [ManualRecaptchaController::class, 'reCaptCha'])->name('captcha');

    /* currency ajax request */
    Route::get('/currency-list', [MoneyTransferController::class, 'currencyList'])->name('currencyList');

    /* Manage User Deposit */
    Route::get('supported-currency', [DepositController::class, 'supportedCurrency'])->name('supported.currency');
    Route::post('payment-request/{transfer?}', [DepositController::class, 'paymentRequest'])->name('payment.request');
    Route::get('deposit-check-amount', [DepositController::class, 'checkAmount'])->name('deposit.checkAmount');

    Route::get('payment-process/{trx_id}', [PaymentController::class, 'depositConfirm'])->name('payment.process');
    Route::post('addFundConfirm/{trx_id}', [PaymentController::class, 'fromSubmit'])->name('addFund.fromSubmit');
    Route::match(['get', 'post'], 'success', [PaymentController::class, 'success'])->name('success');
    Route::match(['get', 'post'], 'failed', [PaymentController::class, 'failed'])->name('failed');
    Route::match(['get', 'post'], 'payment/{code}/{trx?}/{type?}', [PaymentController::class, 'gatewayIpn'])->name('ipn');

    Route::view('transfer-success', 'transfer-success')->name('transfer-success');

    Route::post('khalti/payment/verify/{trx}', [khaltiPaymentController::class, 'verifyPayment'])->name('khalti.verifyPayment');
    Route::post('khalti/payment/store', [khaltiPaymentController::class, 'storePayment'])->name('khalti.storePayment');

    /*=== Frontend Blog Manage ====*/
    Route::get('blogs', [FrontendController::class, 'blog'])->name('blog');
    Route::get('blog/{slug}', [FrontendController::class, 'blogDetails'])->name('blog.details');
    Route::get('blog-search', [FrontendController::class, 'blogSearch'])->name('blogSearch');
    Route::post('category-search', [FrontendController::class, 'categorySearch'])->name('categorySearch');
    Route::get('blog/category-wise/{slug}/{id}', [FrontendController::class, 'categoryWiseBlog'])->name('blog.categoryWise');

    Auth::routes();
    Route::group(['middleware' => ['guest']], function () {
        Route::get('register/{sponsor?}', [RegisterController::class, 'sponsor'])->name('register.sponsor');
    });

    Route::post('/contact', [FrontendController::class, 'contactSend'])->name('contact.send');
    Route::get('/language/{code?}', [FrontendController::class, 'language'])->name('language');

    /*= Frontend Manage Controller =*/
    Route::get("/{slug?}", [FrontendController::class, 'page'])->name('page');


});






