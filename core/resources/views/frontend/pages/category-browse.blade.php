@extends('frontend.layout.master')
@section('site_title',__('Browse Categories'))
@section('meta_title'){{ __('Browse Categories') }}@endsection

@section('content')
    <main>
        <x-breadcrumb.user-profile-breadcrumb :title=" __('Browse Categories') " :innerTitle=" __('Browse Categories') ?? '' "/>

        <div class="section-bg-2 pat-50 pab-100">
            <div class="container">
                <div class="row g-4">
                    @foreach($categories as $category)
                        <div class="col-12">
                            <h4 class="mb-3">{{ $category->category }}</h4>
                            @if($category->sub_categories->count())
                                <div class="row g-2">
                                    @foreach($category->sub_categories as $sub)
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <a class="btn btn-outline-secondary w-100"
                                               href="{{ route('subcategory.projects', $sub->slug) }}">{{ $sub->sub_category }}</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ __('No subcategories yet.') }}</p>
                            @endif
                            <hr class="my-4" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
@endsection


