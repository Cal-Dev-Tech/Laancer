<?php


namespace plugins\PageBuilder\Addons\Project;

use App\Models\JobPost;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use plugins\PageBuilder\Fields\ColorPicker;
use App\Service;
use plugins\PageBuilder\Fields\Slider;
use plugins\PageBuilder\Fields\Number;
use plugins\PageBuilder\Fields\Text;
use plugins\PageBuilder\PageBuilderBase;
use plugins\PageBuilder\Traits\LanguageFallbackForPageBuilder;
use plugins\PageBuilder\Fields\Select;


class PopularProjectOne extends PageBuilderBase
{
    use LanguageFallbackForPageBuilder;

    public function preview_image()
    {
        return 'home-page/popular-project-one.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();


        $output .= Text::get([
            'name' => 'title',
            'label' => __('Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);

        $output .= Number::get([
            'name' => 'items',
            'label' => __('Items'),
            'value' => $widget_saved_values['items'] ?? null,
            'info' => __('Enter how many items you want to show in frontend. Max 6 for Grid layout.'),
        ]);

        $output .= Select::get([
            'name' => 'order_by',
            'label' => __('Order By'),
            'options' => [
                'latest' => __('Latest First'),
                'oldest' => __('Oldest First'),
                'random' => __('Random'),
                'title_asc' => __('Title A → Z'),
                'title_desc' => __('Title Z → A'),
            ],
            'value' => $widget_saved_values['order_by'] ?? 'latest',
            'info' => __('Choose how projects should be ordered.'),
        ]);

        $output .= Select::get([
            'name' => 'layout_type',
            'label' => __('Layout Type'),
            'options' => [
                'grid' => __('Grid'),
                'slider' => __('Slider'),
            ],
            'value' => $widget_saved_values['layout_type'] ?? 'slider',
            'info' => __('Choose whether to display projects in grid or slider layout.'),
        ]);

        $output .= Slider::get([
            'name' => 'padding_top',
            'label' => __('Padding Top'),
            'value' => $widget_saved_values['padding_top'] ?? 260,
            'max' => 500,
        ]);

        $output .= Slider::get([
            'name' => 'padding_bottom',
            'label' => __('Padding Bottom'),
            'value' => $widget_saved_values['padding_bottom'] ?? 190,
            'max' => 500,
        ]);
        $output .= ColorPicker::get([
            'name' => 'section_bg',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['section_bg'] ?? null,
            'info' => __('select color you want to show in frontend'),
        ]);

        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }


    public function frontend_render()
    {
        $settings = $this->get_settings();
        $title = $settings['title'];
        $items = $settings['items'] ?? 5;
        $order_by = $settings['order_by'] ?? 'latest';
        $layout_type = $settings['layout_type'] ?? 'grid';
        $padding_top = $settings['padding_top'];
        $padding_bottom = $settings['padding_bottom'];
        $section_bg = $settings['section_bg'] ?? '';

        if ($layout_type === 'grid' && $items > 6) {
            $items = 6;
        }

        $top_projects = Project::select(
            'id',
            'title',
            'slug',
            'user_id',
            'basic_regular_charge',
            'basic_discount_charge',
            'basic_delivery',
            'description',
            'image',
            'load_from'
        )
            ->where('project_on_off', '1')
            ->where('status', '1')
            ->whereHas('project_creator')
            ->withCount(['orders' => function ($q) {
                $q->where('status', 3)
                    ->where('is_project_job', 'project');
            }])
            ->orderBy('orders_count', 'DESC')
            ->take(100)
            ->get();

        switch ($order_by) {
            case 'latest':
                $top_projects = $top_projects->sortByDesc('id')->take($items);
                break;
            case 'oldest':
                $top_projects = $top_projects->sortBy('id')->take($items);
                break;
            case 'title_asc':
                $top_projects = $top_projects->sortBy('title')->take($items);
                break;
            case 'title_desc':
                $top_projects = $top_projects->sortByDesc('title')->take($items);
                break;
            case 'random':
                $top_projects = $top_projects->shuffle()->take($items);
                break;
            default:
                $top_projects = $top_projects->take($items);
                break;
        }

        return  $this->renderBlade('projects.popular-projects-one', compact(['title', 'items', 'padding_top', 'padding_bottom', 'section_bg', 'top_projects', 'layout_type']));
    }

    public function addon_title()
    {
        return __('Popular Project: 01');
    }
}
