@extends('admin.layouts.app')
@section('page_title', __('Edit Advertisement'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-4">{{ __('Edit Advertisement') }}</h3>

            <!-- Edit Form -->
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Update Advertisement Image') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ads.update', $ad->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="ads" class="form-label">{{ __('Upload New Image') }}</label>
                            <input type="file" name="image" id="ads" class="form-control" accept="image/*" onchange="previewImage(event)">
                        </div>

                        <!-- Image Preview -->
                        <div class="mt-3">
                            <h5>{{ __('Preview Image') }}</h5>
                            <img id="imagePreview" 
                                 src="{{ $ad->image ? asset('storage/app/public/' . $ad->image) : '' }}" 
                                 alt="Current Ad" 
                                 class="img-fluid" 
                                 width="150"
                                 style="display: {{ $ad->image ? 'block' : 'none' }}; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">{{ __('Update') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for live image preview -->
<script>
    function previewImage(event) {
        const fileInput = event.target;
        const file = fileInput.files[0];
        const imagePreview = document.getElementById('imagePreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
        }
    }
</script>
@endsection
