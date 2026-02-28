<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ManageMenu;
use App\Models\Page;
use App\Rules\AlphaDashWithoutSlashes;
use App\Traits\Upload;
use Http\Client\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use MatthiasMullie\Minify\JS;

class PageController extends Controller
{
    use Upload;

    public function index()
    {
        $theme = basicControl()->theme;
        $data['allTemplate'] = getThemesNames();
        abort_if(!in_array($theme, getThemesNames()), 404);
        $data['allLanguage'] = Language::select('id', 'name', 'short_name', 'flag', 'flag_driver')->where('status', 1)->orderBy('default_status', 'desc')->get();
        $defaultLanguage = Language::where('default_status', true)->first();
        $data['allPages'] = Page::with(['details' => function ($query) use ($defaultLanguage) {
            $query->where('language_id', $defaultLanguage->id);
        }])->whereNull('custom_link')->get();
        return view("admin.frontend_management.page.index", $data, compact("theme", 'defaultLanguage'));
    }

    public function create($theme)
    {
        abort_if(!in_array($theme, getThemesNames()), 404);
        $data['url'] = url('/') . "/";
        $data["sections"] = getPageSections();
        $data['defaultLanguage'] = Language::where('default_status', true)->first();
        return view("admin.frontend_management.page.create", $data, compact('theme'));
    }

    public function store(Request $request, $theme)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:100|unique:page_details,name',
            'slug' => ['required', 'unique:pages,slug', 'min:1', 'max:100',
                new AlphaDashWithoutSlashes(),
                Rule::notIn(['login', 'register', 'signin', 'signup', 'sign-in', 'sign-up'])],
            'page_content' => 'required|string|min:3',
            'breadcrumb_status' => 'nullable|integer|in:0,1',
            'breadcrumb_image' => ($request->input('breadcrumb_status') == 1) ? 'required|mimes:jpg,png,jpeg|max:2048' : 'nullable|mimes:jpg,png,jpeg|max:2048',
            'status' => 'nullable|integer|in:0,1',

        ]);

        if ($request->hasFile('breadcrumb_image')) {
            $image = $this->fileUpload($request->breadcrumb_image, config('filelocation.pageImage.path'), null, null, 'webp', 80);
            if ($image) {
                $breadCrumbImage = $image['path'];
                $breadCrumbImageDriver = $image['driver'];
            }
        }

        $sections = preg_match_all('/\[\[([^\]]+)\]\]/', strtolower($request->page_content), $matches) ? $matches[1] : null;

        try {
            $response = Page::create([
                "name" => strtolower($request->name),
                "slug" => $request->slug,
                "template_name" => $theme,
                "breadcrumb_image" => $breadCrumbImage ?? null,
                "breadcrumb_image_driver" => $breadCrumbImageDriver ?? 'local',
                "breadcrumb_status" => $request->breadcrumb_status,
                "status" => $request->status,
                "type" => 0
            ]);

            if (!$response) {
                throw new \Exception("Something went wrong, Please Try again");
            }

            $response->details()->create([
                "name" => $request->name,
                'language_id' => $request->language_id,
                'content' => $request->page_content,
                'sections' => $sections,
            ]);

            return redirect()->route('admin.page.index', $theme)->with('success', 'Page Saved Successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

    }

    public function edit($id, $theme, $language = null)
    {
        abort_if(!in_array($theme, getThemesNames()), 404);
        $page = Page::with(['details' => function ($query) use ($language) {
            $query->where('language_id', $language);
        }])->where('id', $id)->first();

        $data["sections"] = getPageSections();
        $data['pageEditableLanguage'] = Language::where('id', $language)->select('id', 'name', 'short_name')->first();
        $data['allLanguage'] = Language::select('id', 'name', 'short_name', 'flag', 'flag_driver')->where('status', 1)->get();
        return view("admin.frontend_management.page.edit", $data, compact('page', 'language', 'theme'));
    }

    public function update(Request $request, $id, $theme)
    {
        if ($request->status == 0) {
            $menu = $request->name;
            $menuHeader = ManageMenu::where('menu_section', 'header')->first();
            $nestedArr = $menuHeader->menu_items;
            removeValue($nestedArr, strtolower($menu));
            $menuHeader->menu_items = $nestedArr;
            $menuHeader->save();

            $menuFooter = ManageMenu::where('menu_section', 'footer')->first();

            $nestedArr2 = $menuFooter->menu_items;
            removeValue($nestedArr2, strtolower($menu));
            $menuHeader->menu_items = $nestedArr2;
            $menuHeader->save();
        }

        $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:100',
                Rule::unique('page_details', 'page_id')->ignore($id),
            ],
            'slug' => ['required', 'min:1', 'max:100',
                new AlphaDashWithoutSlashes(),
                Rule::unique('pages', 'slug')->ignore($id),
                Rule::notIn(['login', 'register', 'signin', 'signup', 'sign-in', 'sign-up'])
            ],
            'page_content' => 'required|string|min:3',
            'breadcrumb_status' => 'nullable|integer|in:0,1',
            'breadcrumb_image' => ($request->input('breadcrumb_status') == 1) ? 'sometimes|required|mimes:jpg,png,jpeg|max:2048' : 'nullable|mimes:jpg,png,jpeg|max:2048',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $page = Page::findOrFail($id);
            $sections = preg_match_all('/\[\[([^\]]+)\]\]/', strtolower($request->page_content), $matches) ? $matches[1] : null;

            if ($request->hasFile('breadcrumb_image')) {
                $image = $this->fileUpload($request->breadcrumb_image, config('filelocation.pagesImage.path'), null, null, 'webp', 80, $page->breadcrumb_image, $page->breadcrumb_image_driver);
                throw_if(empty($image['path']), 'Image could not be uploaded.');
                $breadCrumbImage = $image['path'];
                $breadCrumbImageDriver = $image['driver'] ?? 'local';
            }

            $response = $page->update([
                "slug" => $request->slug,
                "template_name" => $theme,
                "breadcrumb_image" => $breadCrumbImage ?? $page->breadcrumb_image,
                "breadcrumb_image_driver" => $breadCrumbImageDriver ?? $page->breadcrumb_image_driver,
                "breadcrumb_status" => $request->breadcrumb_status,
                "status" => $request->status,
            ]);

            if (!$response) {
                throw new \Exception("Something went wrong, Please Try again");
            }

            $page->details()->updateOrCreate([
                'language_id' => $request->language_id,
            ],
                [
                    "name" => $request->name,
                    'content' => $request->page_content,
                    'sections' => $sections,
                ]
            );

            return redirect()->route('admin.page.index', $theme)->with('success', 'Page Updated Successfully');

        } catch (Exception $e) {
            if (isset($breadCrumbImage, $breadCrumbImageDriver))
                $this->fileDelete($breadCrumbImageDriver, $breadCrumbImage);
            return back()->with('error', $e->getMessage());
        }

    }

    public function delete(Request $request, $id)
    {
        try {
            $page = Page::where('id', $id)->firstOr(function () {
                throw new \Exception('Something went wrong, Please try again');
            });

            $headerMenu = ManageMenu::where('menu_section', 'header')->first();
            $footerMenu = ManageMenu::where('menu_section', 'footer')->first();
            $lookingKey = $page->name;
            $headerMenu->update([
                'menu_items' => filterCustomLinkRecursive($headerMenu->menu_items, $lookingKey)
            ]);
            $footerMenu->update([
                'menu_items' => filterCustomLinkRecursive($footerMenu->menu_items, $lookingKey)
            ]);
            $this->fileDelete($page->seo_meta_image_driver, $page->seo_meta_image);
            $page->delete();
            $page->details()->delete();

            return back()->with('success', 'Page deleted successfully');

        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function editStaticPage($id, $theme, $language = null)
    {
        $data['allLanguage'] = Language::select('id', 'name', 'short_name', 'flag', 'flag_driver')->where('status', 1)->get();
        $data['pageEditableLanguage'] = Language::where('id', $language)->select('id', 'name', 'short_name')->first();
        $data['page'] = Page::with(['details' => function ($query) use ($language) {
            $query->where('language_id', $language);
        }])->where('id', $id)->first();

        return view("admin.frontend_management.page.edit_static", $data);
    }

    public function updateStaticPage(Request $request, $id, $theme)
    {

        $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:100',
                Rule::unique('page_details', 'page_id')->ignore($id),
            ],
            'language_id' => 'required|integer|exists:languages,id',
            'breadcrumb_status' => 'nullable|integer|in:0,1',
            'breadcrumb_image' => ($request->input('breadcrumb_status') == 1) ? 'sometimes|required|mimes:jpg,png,jpeg|max:2048' : 'nullable|mimes:jpg,png,jpeg|max:2048',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {

            $page = Page::findOrFail($id);

            if ($request->hasFile('breadcrumb_image')) {
                $image = $this->fileUpload($request->breadcrumb_image, config('filelocation.pagesImage.path'), null, null, 'webp', 80, $page->breadcrumb_image, $page->breadcrumb_image_driver);
                throw_if(empty($image['path']), 'Image could not be uploaded.');
            }

            $response = $page->update([
                "breadcrumb_image" => $image['path'] ?? $page->breadcrumb_image,
                "breadcrumb_image_driver" => $image['driver'] ?? $page->breadcrumb_image_driver,
                "breadcrumb_status" => $request->breadcrumb_status,
                "status" => $request->status,
            ]);

            if (!$response) {
                throw new \Exception("Something went wrong, Please Try again");
            }

            $page->details()->updateOrCreate([
                'language_id' => $request->language_id,
            ],
                [
                    "name" => $request->name,
                ]
            );
            return redirect()->route('admin.page.index', $theme)->with('success', 'Static Page Updated Successfully');
        } catch (Exception $e) {
            if (isset($image['path'], $image['driver']))
                $this->fileDelete($image['driver'], $image['path']);
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateSlug(Request $request)
    {
        $rules = [
            "pageId" => "required|exists:pages,id",
            "newSlug" => ["required", "min:1", "max:100",
                new AlphaDashWithoutSlashes(),
                Rule::unique('pages', 'slug')->ignore($request->pageId),
                Rule::notIn(['login', 'register', 'signin', 'signup', 'sign-in', 'sign-up'])
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $pageId = $request->pageId;
        $newSlug = $request->newSlug;
        $page = Page::find($pageId);

        if (!$page) {
            return back()->with("error", "Page not found");
        }

        $page->update([
            'slug' => $newSlug
        ]);

        return response([
            'success' => true,
            'slug' => $page->slug
        ]);
    }

    public function pageSEO($id)
    {
        $data['page'] = Page::findOrFail($id);
        return view("admin.frontend_management.page.seo", $data);
    }

    public function pageSeoUpdate(Request $request, $id)
    {
        $request->validate([
            'page_title' => 'required|string|min:3|max:100',
            'meta_title' => 'required|string|min:3|max:100',
            'meta_keywords' => 'required|array',
            'meta_keywords.*' => 'required|string|min:1|max:300',
            'meta_description' => 'required|string|min:1|max:300',
            'og_description' => 'nullable|string|min:1|max:300',
            'meta_robots' => 'nullable',
            'seo_meta_image' => 'nullable|mimes:jpeg,png,jpeg|max:2048'
        ]);

        try {
            $pageSEO = Page::findOrFail($id);

            if ($request->hasFile('seo_meta_image')) {

                try {
                    $image = $this->fileUpload($request->seo_meta_image, config('filelocation.seo.path'), null, null, 'webp', 80, $pageSEO->seo_seo_meta_image, $pageSEO->seo_seo_meta_image_driver);
                    throw_if(empty($image['path']), 'Image path not found');
                } catch (\Exception $exp) {
                    return back()->with('error', 'Meta image could not be uploaded.');
                }
            }

            $pageSEO->update([
                'page_title' => $request->page_title,
                'meta_title' => $request->meta_title,
                'meta_keywords' => $request->meta_keywords,
                'meta_description' => $request->meta_description,
                'og_description' => $request->og_description,
                'meta_robots' => $request->meta_robots,
                'seo_meta_image' => $image['path'] ?? $pageSEO->seo_meta_image,
                'seo_meta_image_driver' => $image['driver'] ?? $pageSEO->seo_meta_image_driver,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Seo has been updated.');

    }

}
