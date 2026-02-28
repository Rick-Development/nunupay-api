<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{

    public function index(Request $request)
    {
        $query = BlogCategory::latest();
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }
        $data['cat'] = $query->paginate(basicControl()->paginate);

        return view('admin.blogs.blog_category', $data);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories',
        ]);
        $category = new BlogCategory();
        $category->name = $request->input('name');
        $category->save();

        return back()->with('success', 'Category created successfully');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $id,
        ]);

        $category = BlogCategory::find($id);
        $category->name = $request->input('name');

        if ($request->has('status')) {
            $category->status = $request->input('status');
        }
        $category->save();

        return back()->with('success', 'Category updated successfully');
    }


    public function destroy($id)
    {
        $category = BlogCategory::find($id);

        if ($category->blogs()->exists()) {
            return back()->with('error', 'Cannot delete. Blogs associated.');
        }
        $category->delete();

        return back()->with('success', 'Category Deleted successfully');
    }
}
