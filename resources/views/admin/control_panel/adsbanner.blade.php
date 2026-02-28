@extends('admin.layouts.app')
@section('page_title', __('Manage Advertisements'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-4">{{ __('Advertisement Banner') }}</h3>

            <!-- Display Existing Ads -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>{{ __('Current Advertisements') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($ads as $ad)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <img src="{{ asset('storage/ads/' . $ad->image) }}" alt="Ad Image" class="card-img-top">
                                    <div class="card-footer text-center">
                                        <form action="{{ route('admin.ads.destroy', $ad->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this ad?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center w-100">{{ __('No advertisements found.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Upload New Ads -->
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Upload New Advertisements') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ads.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="ads" class="form-label">{{ __('Upload Images') }}</label>
                            <input type="file" name="images[]" id="ads" class="form-control" multiple required>
                            <small class="text-muted">{{ __('You can upload up to 5 images at a time.') }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
