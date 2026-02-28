<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogDetails;
use App\Models\Language;
use App\Traits\Upload;
use Illuminate\Http\Request;

use App\Rules\AlphaDashWithoutSlashes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    use Upload;

    public function index(Request $request)
    {
        $data['defaultLanguage'] = Language::where('default_status', true)->first();
        $data['allLanguage'] = Language::select('id', 'name', 'short_name', 'flag', 'flag_driver')->where('status', 1)->get();

        $query = Blog::with('category', 'details')->latest();

        if ($request->has('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->whereHas('category', fn ($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('details', fn ($q) => $q->where('title', 'like', "%$search%")
                        ->orWhere('author_name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%"));
            });
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $data['blogs'] = $query->paginate(basicControl()->paginate);

        return view('admin.blogs.list', $data);
    }

    public function create()
    {
        $data['blogCategory'] = BlogCategory::orderBy('id', 'desc')->get();
        $data['defaultLanguage'] = Language::where('default_status', true)->first();
        $data['allLanguage'] = Language::select('id', 'name', 'short_name', 'flag', 'flag_driver')->where('status', 1)->get();
        return view('admin.blogs.create', $data);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|numeric|not_in:0|exists:blog_categories,id',
            'author_name' => 'required|string',
            'author_title' => 'required|string',
            'title' => 'required|string|min:3|max:200',
            'slug' => 'required|string|min:3|max:200|alpha_dash|unique:blog_details,slug',
            'description' => 'required|string',
            'blog_image' => 'required|mimes:png,jpg,jpeg|max:5000',
            'author_image' => 'required|mimes:png,jpg,jpeg|max:5000',
            'breadcrumb_image' => 'required|mimes:png,jpg,jpeg|max:5000',
        ]);

        $uploadFile = function ($file, $key) {
            $uploadedFile = $this->fileUpload($file, config('filelocation.blog.path'), null, null, 'webp', 80);
            throw_if(empty($uploadedFile['path']), ucfirst($key) . ' could not be uploaded.');
            return $uploadedFile;
        };

        $blogImage = $uploadFile($request->file('blog_image'), 'blog_image');
        $authorImage = $uploadFile($request->file('author_image'), 'author_image');
        $breadcrumbImage = $uploadFile($request->file('breadcrumb_image'), 'breadcrumb_image');

        DB::beginTransaction();
        try {
            $blog = Blog::create(array_merge($validatedData, [
                'category_id' => $validatedData['category_id'],
                'blog_image' => $blogImage['path'],
                'blog_image_driver' => $blogImage['driver'],
                'author_image' => $authorImage['path'],
                'author_image_driver' => $authorImage['driver'],
                'breadcrumb_image' => $breadcrumbImage['path'],
                'breadcrumb_image_driver' => $breadcrumbImage['driver'],
                'breadcrumb_status' => $request->breadcrumb_status,
            ]));

            $blog->details()->create([
                'author_name' => $validatedData['author_name'],
                'author_title' => $validatedData['author_title'],
                'title' => $validatedData['title'],
                'slug' => $validatedData['slug'],
                'language_id' => $request->language_id,
                'description' => $validatedData['description'],
            ]);

            DB::commit();
            return redirect(route('admin.blogs.index'))->with('success', 'Blog saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }

    public function blogEdit($id, $language = null)
    {
        $data['blogDetails'] = BlogDetails::where('blog_id', $id)
            ->where('language_id', $language)
            ->firstOr(function () use ($id) {
                return BlogDetails::where('blog_id', $id)->first();
            });

        if (!$data['blogDetails']) {
            throw new \Exception('Blog not found');
        }

        $blog = $data['blogDetails']->blog;

        $data['pageEditableLanguage'] = Language::where('id', $language)->select('id', 'name', 'short_name')->first();
        $data['defaultLanguage'] = Language::where('default_status', true)->first();
        $data['blogCategory'] = BlogCategory::orderBy('id', 'desc')->get();
        $data['allLanguage'] = Language::select('id', 'name', 'short_name', 'flag', 'flag_driver')->where('status', 1)->get();
        return view('admin.blogs.edit', $data, compact('blog', 'language'));
    }

    public function blogUpdate(Request $request, $id, $language)
    {
        $languageId = $request->language_id;
        $request->validate([
            'category_id' => 'required|numeric|not_in:0|exists:blog_categories,id',
            'author_name' => 'required|string',
            'author_title' => 'required|string',
            'title' => 'required|string|min:3|max:200',
            'slug' => [
                'required', 'min:1', 'max:100',
                new AlphaDashWithoutSlashes(),
                Rule::unique('blog_details', 'slug')
                    ->ignore($id, 'blog_id')
                    ->where('language_id', $languageId),

                Rule::notIn(['login', 'register', 'signin', 'signup', 'sign-in', 'sign-up'])
            ],
            'description' => 'nullable|string',
            'blog_image' => 'nullable|mimes:png,jpg,jpeg|max:5000',
            'author_image' => 'nullable|mimes:png,jpg,jpeg|max:5000',
            'breadcrumb_image' => 'nullable|mimes:png,jpg,jpeg|max:5000',
            'status' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();
            $blog = Blog::with("details")->where('id', $id)->firstOrFail();
            $updateData = [
                'category_id' => $request->category_id,
                'status' => $request->status,
                'breadcrumb_status' => $request->breadcrumb_status,
            ];
            if ($request->hasFile('blog_image')) {
                $blogImage = $this->fileUpload($request->blog_image, config('filelocation.blog.path'), null, null, 'webp', 80,$blog->blog_image,$blog->blog_image_driver);
                throw_if(empty($blogImage['path']), 'Blog image could not be uploaded.');
                $updateData['blog_image'] = $blogImage['path'];
                $updateData['blog_image_driver'] = $blogImage['driver'];
            }
            if ($request->hasFile('author_image')) {
                $authorImage = $this->fileUpload($request->author_image, config('filelocation.blog.path'), null, null, 'webp', 80,$blog->author_image,$blog->author_image_driver);
                throw_if(empty($authorImage['path']), 'Author image could not be uploaded.');
                $updateData['author_image'] = $authorImage['path'];
                $updateData['author_image_driver'] = $authorImage['driver'];
            }
            if ($request->hasFile('breadcrumb_image')) {
                $breadcrumbImage = $this->fileUpload($request->breadcrumb_image, config('filelocation.blog.path'), null, null, 'webp', 80,$blog->breadcrumb_image,$blog->breadcrumb_image_driver);
                throw_if(empty($breadcrumbImage['path']), 'Breadcrumb image could not be uploaded.');
                $updateData['breadcrumb_image'] = $breadcrumbImage['path'];
                $updateData['breadcrumb_image_driver'] = $breadcrumbImage['driver'];
            }
            $blog->update($updateData);

            $blog->details()->updateOrCreate(
                ['language_id' => $request->language_id],
                [
                    'author_name' => $request->author_name,
                    'author_title' => $request->author_title,
                    'title' => $request->title,
                    'slug' => $request->slug,
                    'description' => $request->description,
                ]
            );
            DB::commit();
            return redirect(route('admin.blogs.index'))->with('success', 'Blog updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy(Request $request, string $id)
    {
        try {
            $blog = Blog::findOrFail($id);

            if (!$blog) {
                throw new \Exception('No blog data found.');
            }
            DB::beginTransaction();
            $this->fileDelete($blog->blog_image_driver, $blog->blog_image);
            $this->fileDelete($blog->author_image_driver, $blog->author_image);
            $this->fileDelete($blog->breadcrumb_image_driver, $blog->breadcrumb_image);

            $blog->details()->delete();
            $blog->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Blog deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function slugUpdate(Request $request)
    {
        $rules = [
            "blogId" => "required|exists:blogs,id",
            "newSlug" => ["required", "min:1", "max:100",
                new AlphaDashWithoutSlashes(),
                Rule::unique('blog_details', 'slug')->ignore($request->blogId),
                Rule::notIn(['login', 'register', 'signin', 'signup', 'sign-in', 'sign-up'])
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $blogId = $request->blogId;
        $newSlug = $request->newSlug;
        $blog = Blog::find($blogId);

        if (!$blog) {
            return back()->with("error", "Page not found");
        }

        $blog->details()->update([
            'slug' => $newSlug
        ]);

        return response([
            'success' => true,
            'slug' => $blog->slug
        ]);
    }

    public function blogSeo(Request $request, $id)
    {
        try {
            $blog = Blog::with("details")->where('id', $id)->firstOr(function () {
                throw new \Exception('Blog not found');
            });
            return view('admin.blogs.seo', compact('blog'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function blogSeoUpdate(Request $request, $id)
    {
        $request->validate([
            'page_title' => 'required|string|min:3|max:100',
            'meta_title' => 'required|string|min:3|max:100',
            'meta_keywords' => 'required|array',
            'meta_keywords.*' => 'required|string|min:1|max:300',
            'meta_description' => 'required|string|min:1|max:300',
            'seo_meta_image' => 'sometimes|required|mimes:jpeg,png,jpeg|max:2048'
        ]);

        $blog = Blog::with("details")->where('id', $id)->firstOr(function () {
            throw new \Exception('Blog not found');
        });

        if ($request->hasFile('seo_meta_image')) {
            try {
                $image = $this->fileUpload($request->seo_meta_image, config('filelocation.pageSeo.path'), config('filesystems.default'), null, 'webp', 80,$blog->meta_image_driver, $blog->meta_image );
                if ($image) {
                    $pageSEOImage = $image['path'];
                    $pageSEODriver = $image['driver'] ?? 'local';
                }
            } catch (\Exception $exp) {
                return back()->with('error', 'Meta image could not be uploaded.');
            }
        }

        $blog->update([
            'page_title' => $request->page_title,
            'meta_title' => $request->meta_title,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
            'meta_image' => $pageSEOImage ?? $blog->meta_image,
            'meta_image_driver' => $pageSEODriver ?? $blog->meta_image_driver,
        ]);

        return back()->with('success', 'Seo has been updated.');
    }


}
