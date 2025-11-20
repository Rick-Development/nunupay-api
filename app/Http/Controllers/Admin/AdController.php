<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Ad;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::all();
        return view('admin.ads.index', compact('ads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        foreach ($request->file('images') as $image) {
            $path = $image->store('ads', 'public');

            Ad::create([
                'image' => $path, // Save as 'ads/filename.jpg'
            ]);
        }

        return redirect()->route('admin.ads.index')->with('success', __('Advertisements uploaded successfully.'));
    }

    public function edit($id)
    {
        $ad = Ad::findOrFail($id);
        return view('admin.ads.edit', compact('ad'));
    }

    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete the old image from storage
            Storage::disk('public')->delete($ad->image); // No need to specify 'ads/' since it's already in the path

            // Store the new image
            $path = $request->file('image')->store('ads', 'public');

            // Update the ad with the new image
            $ad->update(['image' => $path]);
        }

        return redirect()->route('admin.ads.index')->with('success', __('Advertisement updated successfully.'));
    }

    public function destroy($id)
    {
        $ad = Ad::findOrFail($id);

        // Delete the image file from storage
        Storage::disk('public')->delete($ad->image);
        $ad->delete();

        return redirect()->route('admin.ads.index')->with('success', __('Advertisement deleted successfully.'));
    }
}