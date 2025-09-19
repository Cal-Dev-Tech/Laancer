<!-- Choose area starts -->
<section class="choose-area" data-padding-top="{{$padding_top ?? ''}}" data-padding-bottom="{{$padding_bottom ?? ''}}">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-6">
                <div class="choose-contents">
                    <div class="section-title">
                        @if(request()->routeIs('homepage'))
                            <div class="subtitle"> <span> {{ __('Real people. Real results.') }} </span> </div>
                            <h2 class="title"> {{ __('Why teams and freelancers choose Laancer') }}</h2>
                            <p class="section-para">{{ __('We build long‑term partnerships between clients and top talent—with transparent, low fees, fast matching, and human support. Hire confidently or grow your freelance career with work that respects your craft.') }}</p>
                        @else
                            <div class="subtitle"> <span> {{ $subtitle }} </span> </div>
                            <h2 class="title"> {{ $title }}</h2>
                            <p class="section-para">{{ $mini_description }}</p>
                        @endif
                    </div>
                    <ul class="choose-contents-list mt-4">
                        @foreach ($repeater_data['title_'] as $key => $data)
                        <li class="choose-contents-list-item">{{ $repeater_data['title_'][$key] }} </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="choose-wrapper">
                    @if($shape_image_one)
                    <div class="choose-wrapper-thumb-shapes">
                        {!! render_image_markup_by_attachment_id($shape_image_one) !!}
                    </div>
                    @endif
                    @if($thumbnail_image)
                    <div class="choose-wrapper-thumb">
                        {!! render_image_markup_by_attachment_id($thumbnail_image) !!}
                    </div>
                    @endif
                    @if($shape_image_two)
                    <div class="choose-wrapper-shapes">
                        {!! render_image_markup_by_attachment_id($shape_image_two) !!}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Choose area ends -->