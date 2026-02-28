<?php

namespace App\Console\Commands;

use App\Models\CountryCurrency;
use Facades\App\Services\BasicCurl;
use Illuminate\Console\Command;

class CurrencyRateUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:currency-rate-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoint = 'live';
        $currency_layer_access_key = basicControl()->currency_layer_access_key;

        $currency_layer_url = "http://api.currencylayer.com";
        $baseCurrency = basicControl()->base_currency;

        $source = 'USD';

        $currencies = CountryCurrency::wherehas('country',function ($query){
            $query->where('status',1);
        })->select(['id','code','rate'])->get();


        $currenciesArr = $currencies->pluck('code')->toArray();

        $currencyLists = array_unique($currenciesArr);
        $currencyLists = implode(',', $currencyLists);


        $allCurrencyAPIUrl = "$currency_layer_url/$endpoint?access_key=$currency_layer_access_key&source=$source&currencies=$currencyLists";

        $allCurrencyConvert = BasicCurl::curlGetRequest($allCurrencyAPIUrl);

        $allCurrencyConvert = json_decode($allCurrencyConvert);
        $usdToBase = 1;

        if ( $allCurrencyConvert->success) {
            foreach ($currencies as $method) {
                $convert_rate = 0;
                foreach ($allCurrencyConvert->quotes as $key => $rate) {
                    $curCode = substr($key, -3);
                    $curRate = round($rate / $usdToBase, 2);


                    if ($allCurrencyConvert->source == 'USD') {
                        $convert_rate = round(1 / $usdToBase, 2);
                    }
                    if ($curCode == $method->code) {
                        $convert_rate = $curRate;
                        break;
                    }
                }
                $method->rate = $convert_rate;
                $method->save();
            }
        }


    }
}
