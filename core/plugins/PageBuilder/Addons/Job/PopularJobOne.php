<?php


namespace plugins\PageBuilder\Addons\Job;

use App\Models\JobPost;
use plugins\PageBuilder\Fields\ColorPicker;
use App\Service;
use plugins\PageBuilder\Fields\Slider;
use plugins\PageBuilder\Fields\Number;
use plugins\PageBuilder\Fields\Text;
use plugins\PageBuilder\PageBuilderBase;
use plugins\PageBuilder\Traits\LanguageFallbackForPageBuilder;
use plugins\PageBuilder\Fields\Select;


class PopularJobOne extends PageBuilderBase
{
    use LanguageFallbackForPageBuilder;

    public function preview_image()
    {
        return 'home-page/popular-job-one.png';
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
            'info' => __('Choose how jobs should be ordered.'),
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
        $layout_type = $settings['layout_type'] ?? 'slider';
        $padding_top = $settings['padding_top'];
        $padding_bottom = $settings['padding_bottom'];
        $section_bg = $settings['section_bg'] ?? '';

        if ($layout_type === 'grid' && $items > 6) {
            $items = 6;
        }

        $query = JobPost::with('job_creator', 'job_skills')
            ->where('on_off', '1')
            ->where('status', '1')
            ->where('job_approve_request', '1')
            ->whereHas('job_creator');

        switch ($order_by) {
            case 'latest':
                $query->orderBy('id', 'desc');
                break;
            case 'oldest':
                $query->orderBy('id', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'random':
            default:
                $query->inRandomOrder();
                break;
        }

        $jobs = $query->take($items)->get();

        return  $this->renderBlade('jobs.popular-jobs-one', compact(['title', 'padding_top', 'padding_bottom', 'section_bg', 'jobs', 'layout_type']));
    }

    public function addon_title()
    {
        return __('Popular Job: 01');
    }
}
