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
use plugins\PageBuilder\Fields\Repeater;
use plugins\PageBuilder\Fields\Select;
use plugins\PageBuilder\PageBuilderBase;
use plugins\PageBuilder\Traits\LanguageFallbackForPageBuilder;

class SelectedProjectOne extends PageBuilderBase
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

        // Get all available projects for selection
        $all_projects = Project::select('id', 'title', 'slug')
            ->where('project_on_off', '1')
            ->where('status', '1')
            ->whereHas('project_creator')
            ->orderBy('title', 'asc')
            ->get();

        $project_options = [];
        foreach ($all_projects as $project) {
            $project_options[$project->id] = $project->title;
        }

        $output .= Select::get([
            'name' => 'project_1',
            'label' => __('Select Project 1'),
            'options' => ['' => __('-- Select Project --')] + $project_options,
            'value' => $widget_saved_values['project_1'] ?? '',
        ]);

        $output .= Select::get([
            'name' => 'project_2',
            'label' => __('Select Project 2'),
            'options' => ['' => __('-- Select Project --')] + $project_options,
            'value' => $widget_saved_values['project_2'] ?? '',
        ]);

        $output .= Select::get([
            'name' => 'project_3',
            'label' => __('Select Project 3'),
            'options' => ['' => __('-- Select Project --')] + $project_options,
            'value' => $widget_saved_values['project_3'] ?? '',
        ]);

        $output .= Select::get([
            'name' => 'project_4',
            'label' => __('Select Project 4'),
            'options' => ['' => __('-- Select Project --')] + $project_options,
            'value' => $widget_saved_values['project_4'] ?? '',
        ]);

        $output .= Select::get([
            'name' => 'project_5',
            'label' => __('Select Project 5'),
            'options' => ['' => __('-- Select Project --')] + $project_options,
            'value' => $widget_saved_values['project_5'] ?? '',
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


        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $settings       = $this->get_settings();

        $title          = $settings['title'] ?? '';
        $padding_top    = $settings['padding_top'] ?? 0;
        $padding_bottom = $settings['padding_bottom'] ?? 0;
        $section_bg     = $settings['section_bg'] ?? '';

        $selected_project_ids = [];
        foreach ($settings as $key => $value) {
            if (strpos($key, 'project_') === 0 && !empty($value)) {
                $selected_project_ids[] = (int) $value;
            }
        }

        $items = count($selected_project_ids);

        // Fetch projects
        $top_projects = collect();
        if (!empty($selected_project_ids)) {
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
                ->whereIn('id', $selected_project_ids)
                ->withCount([
                    'orders' => function ($query) {
                        $query->where('status', 3)
                            ->where('is_project_job', 'project');
                    }
                ])
                ->get()
                ->sortBy(function ($project) use ($selected_project_ids) {
                    return array_search($project->id, $selected_project_ids);
                })
                ->values();
        }

        return $this->renderBlade('projects.selected-projects-one', compact(
            'title',
            'items',
            'padding_top',
            'padding_bottom',
            'section_bg',
            'top_projects'
        ));
    }

    public function addon_title()
    {
        return __('Selected Project: 01');
    }
}
