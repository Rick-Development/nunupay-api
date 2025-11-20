<?php

namespace App\Providers;

use App\Models\ContentDetails;
use App\Models\Country;
use App\Models\Language;
use App\Models\ManageMenu;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillTransportFactory;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try{
            DB::connection()->getPdo();

            Blade::directive('active', function ($route) {
                return "<?php echo request()->routeIs($route) ? 'active' : ''; ?>";
            });

            Blade::directive('serial', function () {
                return '<?= $loop->iteration; ?>';
            });

            $data['basicControl'] = basicControl();
            $data['theme'] = template();
            $data['themeTrue'] = template(true);
            $data['country'] = Country::where('status', 1)->where('receive_from',1)->get();
            View::share($data);

            view()->composer([
                $data['theme'] . 'partials.header',
                $data['theme'] . 'sections.footer',
                $data['theme'] . 'page',
                $data['country'] . 'sections.countries'
            ], function ($view) {

                $footerMenu = ManageMenu::where('menu_section', 'footer')->first();
                $view->with('footerMenu', $footerMenu);

                $section = 'footer';
                $footer_section1 = ContentDetails::with('content')
                    ->whereHas('content', function ($query) use ($section) {
                        $query->where('name', $section);
                    })
                    ->get();
                $singleContent = $footer_section1->where('content.name', $section)->where('content.type', 'single')->first() ?? [];
                $multipleContents = $footer_section1->where('content.name', $section)->where('content.type', 'multiple')->values()->map(function ($multipleContentData) {
                    return collect($multipleContentData->description)->merge($multipleContentData->content->only('media'));
                });
                $data = [
                    'single' => $singleContent ? collect($singleContent->description ?? [])->merge($singleContent->content->only('media')) : [],
                    'multiple' => $multipleContents,
                    'languages' => Language::all()
                ];
                $view->with('footer', $data);
            });


            if (basicControl()->force_ssl == 1) {
                if ($this->app->environment('production') || $this->app->environment('local')) {
                    \URL::forceScheme('https');
                }
            }

            Mail::extend('sendinblue', function () {
                return (new SendinblueTransportFactory)->create(
                    new Dsn(
                        'sendinblue+api',
                        'default',
                        config('services.sendinblue.key')
                    )
                );
            });

            Mail::extend('sendgrid', function () {
                return (new SendgridTransportFactory)->create(
                    new Dsn(
                        'sendgrid+api',
                        'default',
                        config('services.sendgrid.key')
                    )
                );
            });

            Mail::extend('mandrill', function () {
                return (new MandrillTransportFactory)->create(
                    new Dsn(
                        'mandrill+api',
                        'default',
                        config('services.mandrill.key')
                    )
                );
            });
        } catch (\Exception $e){

        }

    }
}
