<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogDetails;
use App\Models\Content;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageDetail;
use App\Traits\Frontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FrontendController extends Controller
{
    use Frontend;

    public function __construct()
    {
        $this->theme = template();
    }

    public function page($slug = '/')
    {
        $existingSlugs = collect([]);
        DB::table('pages')->select('slug')->get()->map(function ($item) use ($existingSlugs) {
            $existingSlugs->push($item->slug);
        });
        if (!in_array($slug, $existingSlugs->toArray())) {
            abort(404);
        }

        try {
            $selectedTheme = basicControl()->theme;
            $pageDetails = PageDetail::with('page')
                ->whereHas('page', function ($query) use ($slug, $selectedTheme) {
                    $query->where(['slug' => $slug, 'template_name' => $selectedTheme]);
                })
                ->firstOrFail();

            $pageSeo = [
                'page_title' => optional($pageDetails->page)->page_title,
                'meta_title' => optional($pageDetails->page)->meta_title,
                'meta_keywords' => optional($pageDetails->page)->meta_keywords,
                'meta_description' => optional($pageDetails->page)->meta_description,
                'og_description' => optional($pageDetails->page)->og_description,
                'meta_robots' => optional($pageDetails->page)->meta_robots,
                'seo_meta_image' => getFile($pageDetails->page?->seo_meta_image_driver,$pageDetails->page?->seo_meta_image),
            ];

            $banner = Page::where('slug', $slug)->select('page_title', 'breadcrumb_image', 'breadcrumb_image_driver', 'breadcrumb_status')->first();

            $sectionsData = $this->getSectionsData($pageDetails->sections, $pageDetails->content, $selectedTheme);

            return view("themes.{$selectedTheme}.page", compact('sectionsData', 'pageSeo', 'banner'));

        } catch (\Exception $exception) {
            return redirect()->route('instructionPage');
        }
    }

    public function language($locale)
    {
        $language = Language::where('short_name', $locale)->first();
        if (!$language) {
            $locale = 'en';
        }
        session()->put('lang', $locale);
        session()->put('rtl', $language ? $language->rtl : 0);
        return redirect()->back();
    }

    public function contactSend(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|max:91',
            'subject' => 'required|max:100',
            'message' => 'required|max:1000',
        ]);
        $requestData = $request->except('_token', '_method');

        $name = $requestData['name'];
        $email_from = $requestData['email'];
        $subject = $requestData['subject'];
        $message = $requestData['message'] . "<br>Regards<br>" . $name;
        $from = $email_from;

        Mail::to(basicControl()->sender_email)->send(new SendMail($from, $subject, $message));
        return back()->with('success', 'Your message has been sent');
    }

    public function blog()
    {
        $data['pageSeo'] = Page::where('name', 'blog')->select('page_title')->first();
        $data['banner'] = Page::where('name', 'blog')->select('page_title', 'breadcrumb_image', 'breadcrumb_image_driver', 'breadcrumb_status')->firstOrFail();
        $data['content'] = Content::with('contentDetails')->where('name', 'blog')->where('type', 'single')->firstOrFail();
        $data['blogs'] = Blog::with('category', 'details')->where('status', 1)
            ->latest()->paginate(6);
        return view($this->theme . 'blog', $data);
    }

    public function blogDetails($slug)
    {
        $data['blogDetails'] = BlogDetails::with('blog')->where('slug', $slug)->firstOrFail();
        $data['banner'] = $data['blogDetails']->blog;
        $data['categories'] = BlogCategory::where('status', 1)->latest()->get();
        $data['recent_blogs'] = Blog::with('details')->where('status', 1)
            ->where('id', '!=', $data['blogDetails']->blog->id)->latest()->get();
        $data['blogCount'] = Blog::whereIn('category_id', $data['categories']->pluck('id'))->where('status', 1)->get()
            ->groupBy('category_id')->map->count();
        return view($this->theme . 'blog_details', $data);
    }

    public function blogSearch(Request $request)
    {
        $search = $request->search;
        $data['banner'] = Page::where('name', 'blog')->select('page_title', 'breadcrumb_image', 'breadcrumb_image_driver', 'breadcrumb_status')->first();
        $data['blogs'] = Blog::with('details', 'category')->where('status', 1)
            ->where(function ($query) use ($search) {
                $query->whereHas('category', fn($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('details', fn($q) => $q->where('title', 'like', "%$search%")
                        ->orWhere('author_name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%"));
            })
            ->latest()->paginate(3);
        return view($this->theme . 'blog', $data);
    }

    public function categoryWiseBlog($slug = 'blog-title', $id)
    {
        $data['banner'] = Page::where('name', 'blog')->select('page_title', 'breadcrumb_image', 'breadcrumb_image_driver', 'breadcrumb_status')->first();
        $data['blogs'] = Blog::with(['details', 'category'])->where('category_id', $id)->where('status', 1)->latest()->paginate(3);
        return view($this->theme . 'blog', $data);
    }


}
