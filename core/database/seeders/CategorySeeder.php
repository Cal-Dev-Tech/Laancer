<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Service\Entities\Category;
use Modules\Service\Entities\SubCategory;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $taxonomy = [
            'Programming & Tech' => [
                'Website Development',
                'CMS Development',
                'E-commerce Development',
                'Mobile App Development',
                'AI & Data',
                'DevOps & Cloud',
            ],
            'Design & Creative' => [
                'Logo & Brand Identity',
                'UI/UX Design',
                'Graphic Design',
                '3D & Motion',
            ],
            'Video & Animation' => [
                'Video Editing',
                'Explainer Videos',
                '2D/3D Animation',
            ],
            'Writing & Translation' => [
                'Copywriting',
                'Content Writing',
                'Technical Writing',
                'Translation',
            ],
            'Digital Marketing' => [
                'SEO',
                'PPC/SEM',
                'Social Media Marketing',
                'Email Marketing',
                'Analytics & Tracking',
            ],
            'Business & Admin' => [
                'Virtual Assistance',
                'Data Entry',
                'Research & Analysis',
                'Customer Support',
                'Bookkeeping',
            ],
            'Engineering & Architecture' => [
                'Architecture',
                'CAD/Product Design',
            ],
            'Music & Audio' => [
                'Voice Over',
                'Podcast Editing',
                'Music Production',
            ],
            'Finance & Accounting' => [
                'Tax & Compliance',
                'Bookkeeping Systems',
            ],
            'Sales & Customer Care' => [
                'Lead Generation',
                'CRM Setup',
            ],
        ];

        foreach ($taxonomy as $categoryName => $subs) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'category' => $categoryName,
                    'short_description' => $categoryName,
                    'status' => 1,
                    'image' => null,
                    'selected_category' => null,
                ]
            );

            foreach ($subs as $subName) {
                SubCategory::updateOrCreate(
                    [
                        'slug' => Str::slug($categoryName.'-'.$subName),
                    ],
                    [
                        'sub_category' => $subName,
                        'category_id' => $category->id,
                        'short_description' => $subName,
                        'status' => 1,
                        'image' => null,
                    ]
                );
            }
        }
    }
}


