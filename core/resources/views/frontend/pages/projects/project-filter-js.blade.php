<script>
    (function ($) {
        "use strict";
        $(document).ready(function () {
            $('.country_select2').select2();
            $('.category_select2').select2();
            $('.subcategory_select2').select2();
            $('.state_select2').select2();
            $('.skills_select2').select2();

            // change category and get subcategory
            $('#subcategory_info').hide();
            $(document).on('change','#category', function() {
                let category = $(this).val();
                let jsonOldCategory = @json(old('subcategory', []));

                $('#subcategory_info').show();
                $.ajax({
                    method: 'post',
                    url: "{{ route('au.subcategory.all') }}",
                    data: {
                        category: category,
                        old_sub_categories: '{{ json_encode(old('subcategory') ?? []) }}'
                    },
                    success: function(res) {
                        if (res.status == 'success') {
                            let all_options = "<option value=''>{{__('Select Sub Category')}}</option>";
                            let all_subcategories = res.subcategories;

                            $.each(all_subcategories, function(index, value) {
                                all_options += `<option ${jsonOldCategory?.includes(value?.id?.toString() ?? 0) ? 'selected=\'selected\'' : ''} value='${value.id}'>${value.sub_category ?? ''}</option>`;
                            });

                            $("#subcategory").next('.select2-container').find('.select2-selection__rendered ').html('');
                            $(".get_subcategory").html(all_options);

                            $("#subcategory_info").html('');
                            if(all_subcategories.length <= 0){
                                $("#subcategory_info").html('<span class="text-danger"> {{ __('No sub categories found for selected category!') }} <span>');
                            }
                        }
                    }
                })
            })

            // change country and get state
            $('#state_info').hide();
            $(document).on('change','#country', function() {
                let country = $(this).val();
                let jsonOldState = @json(old('state', []));

                if(country) {
                    $('#state_info').show();
                    $.ajax({
                        method: 'post',
                        url: "{{ route('au.state.all') }}",
                        data: {
                            country: country,
                            old_states: '{{ json_encode(old('state') ?? []) }}'
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                let all_options = "<option value=''>{{__('Select State')}}</option>";
                                let all_states = res.states;

                                $.each(all_states, function(index, value) {
                                    all_options += `<option ${jsonOldState?.includes(value?.id?.toString() ?? 0) ? 'selected=\'selected\'' : ''} value='${value.id}'>${value.state ?? ''}</option>`;
                                });

                                $("#state").next('.select2-container').find('.select2-selection__rendered ').html('');
                                $(".get_state").html(all_options);

                                $("#state_info").html('');
                                if(all_states.length <= 0){
                                    $("#state_info").html('<span class="text-danger"> {{ __('No states found for selected country!') }} <span>');
                                }
                            }
                        }
                    });
                } else {
                    // Reset state when no country is selected
                    $('#state_info').hide();
                    $("#state").next('.select2-container').find('.select2-selection__rendered ').html('');
                    $(".get_state").html("<option value=''>{{__('Select State')}}</option>");
                }
            });

            //star rating filter
            $(document).on('click', '.active-list .list', function() {
                let ratings = $(".active-list .list");

                ratings.each(function (){
                    $(this).removeClass('active');
                });

                $(this).addClass('active');
                projects();
            });

            $(document).on('keydown', '#job_search_string', function (e) {
                if (e.key === "Enter" || e.which === 13) {
                    e.preventDefault(); 
                    projects();
                }
            });

            $(document).on('click','#job_search_by_text',function(){
                if ($('#job_search_string').val() == '') {
                    return false;
                }else{
                    projects();
                }
            })

            //project filter
            $(document).on('change', '#category, #subcategory, #skills, #country, #state, #level , #delivery_day', function() {
                projects();
            });

            $(document).on('click', '#set_price_range', function() {
                projects();
            });

            // pagination
            $(document).on('click', '.pagination a', function(e){
                e.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                projects(page);
                $('html, body').animate({ scrollTop: 0 }, 500);
            });

            function projects(page = 1){
                let category = $('#category').val();
                let subcategory = $('#subcategory').val();
                let skills = $('#skills').val();
                let country = $('#country').val();
                let state = $('#state').val();
                let level = $('#level').val();
                let min_price = $('#min_price').val();
                let max_price = $('#max_price').val();
                let delivery_day = $('#delivery_day').val();
                let job_search_string = $('#job_search_string').val();
                let get_pro_projects;
                let rating = $('.filter-lists .list.active').attr('data-rating');

                if($('#get_pro_projects').prop('checked')){
                    $('#get_pro_projects').val('1')
                    get_pro_projects = $('#get_pro_projects').val()
                }else{
                    $('#get_pro_projects').val('0')
                    get_pro_projects = $('#get_pro_projects').val()
                }

                $.ajax({
                    url:"{{ route('projects.pagination').'?page='}}" + page,
                    method:'GET',
                    data:{category:category,subcategory:subcategory,skills:skills,country:country,state:state,level:level,min_price:min_price,rating:rating,max_price:max_price,delivery_day:delivery_day,get_pro_projects:get_pro_projects,job_search_string:job_search_string},
                    success:function(res){
                        if(res.status=='nothing'){
                            $('.search_result').html(
                                `<div class="congratulation-area section-bg-2 pat-100 pab-100">
                                    <div class="container">
                                        <div class="congratulation-wrapper">
                                            <div class="congratulation-contents center-text">
                                                <div class="congratulation-contents-icon bg-danger wow  zoomIn animated" data-wow-delay=".5s" style="visibility: visible; animation-delay: 0.5s; animation-name: zoomIn;">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <h4 class="congratulation-contents-title"> {{ __('OPPS!') }} </h4>
                                                <p class="congratulation-contents-para">{{ __('Nothing') }} <strong>{{ __('Found') }}</strong> </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>`);
                        }else{
                            $('.search_result').html(res);
                        }
                    }

                });
            }

            //get pro projects
            $(document).on('change', '#get_pro_projects', function(e){
                e.preventDefault();
                projects();
            });
        });
    }(jQuery));
</script>
