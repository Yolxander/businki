<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = Profile::all();

        if ($profiles->isEmpty()) {
            $this->command->warn('No profiles found. Please run ProfileSeeder first.');
            return;
        }

        $services = [
            [
                'name' => 'Custom Website Design',
                'description' => 'Professional website design with modern UI/UX principles, responsive design, and custom functionality.',
                'category' => 'Web Design',
                'pricing_type' => 'One Time',
                'one_time_price' => 1500.00,
                'duration' => '2-3 weeks',
                'is_active' => true,
            ],
            [
                'name' => 'E-commerce Development',
                'description' => 'Complete e-commerce solution with shopping cart, payment integration, and inventory management.',
                'category' => 'Development',
                'pricing_type' => 'Project-based',
                'project_price' => 3500.00,
                'duration' => '4-6 weeks',
                'is_active' => true,
            ],
            [
                'name' => 'SEO Optimization',
                'description' => 'Comprehensive SEO services including keyword research, on-page optimization, and performance monitoring.',
                'category' => 'SEO',
                'pricing_type' => 'Monthly',
                'monthly_price' => 500.00,
                'duration' => 'Ongoing',
                'is_active' => true,
            ],
            [
                'name' => 'Brand Identity Design',
                'description' => 'Complete brand identity package including logo design, color palette, typography, and brand guidelines.',
                'category' => 'Branding',
                'pricing_type' => 'One Time',
                'one_time_price' => 800.00,
                'duration' => '1-2 weeks',
                'is_active' => true,
            ],
            [
                'name' => 'Content Creation',
                'description' => 'High-quality content creation including blog posts, website copy, and marketing materials.',
                'category' => 'Content',
                'pricing_type' => 'Hourly',
                'hourly_rate' => 75.00,
                'duration' => 'Varies',
                'is_active' => true,
            ],
            [
                'name' => 'Social Media Management',
                'description' => 'Complete social media management including content creation, posting, and community engagement.',
                'category' => 'Marketing',
                'pricing_type' => 'Monthly',
                'monthly_price' => 300.00,
                'duration' => 'Ongoing',
                'is_active' => true,
            ],
            [
                'name' => 'WordPress Development',
                'description' => 'Custom WordPress theme and plugin development with advanced functionality and customization.',
                'category' => 'Development',
                'pricing_type' => 'One Time',
                'one_time_price' => 1200.00,
                'duration' => '3-4 weeks',
                'is_active' => true,
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'Native and cross-platform mobile app development for iOS and Android platforms.',
                'category' => 'Development',
                'pricing_type' => 'Project-based',
                'project_price' => 8000.00,
                'duration' => '8-12 weeks',
                'is_active' => true,
            ],
            [
                'name' => 'Google Ads Management',
                'description' => 'Professional Google Ads campaign management including setup, optimization, and performance tracking.',
                'category' => 'Marketing',
                'pricing_type' => 'Monthly',
                'monthly_price' => 400.00,
                'duration' => 'Ongoing',
                'is_active' => true,
            ],
            [
                'name' => 'Website Maintenance',
                'description' => 'Regular website maintenance including updates, backups, security monitoring, and performance optimization.',
                'category' => 'Development',
                'pricing_type' => 'Monthly',
                'monthly_price' => 150.00,
                'duration' => 'Ongoing',
                'is_active' => true,
            ],
            [
                'name' => 'Email Marketing Campaigns',
                'description' => 'Email marketing campaign design, implementation, and management with analytics and optimization.',
                'category' => 'Marketing',
                'pricing_type' => 'One Time',
                'one_time_price' => 600.00,
                'duration' => '1-2 weeks',
                'is_active' => true,
            ],
            [
                'name' => 'UI/UX Design',
                'description' => 'User interface and user experience design with wireframes, prototypes, and user testing.',
                'category' => 'Web Design',
                'pricing_type' => 'Hourly',
                'hourly_rate' => 85.00,
                'duration' => 'Varies',
                'is_active' => true,
            ],
        ];

        foreach ($profiles as $profile) {
            // Create 3-6 random services for each profile
            $randomServices = collect($services)->random(rand(3, 6));

            foreach ($randomServices as $serviceData) {
                Service::create([
                    'profile_id' => $profile->id,
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'],
                    'category' => $serviceData['category'],
                    'pricing_type' => $serviceData['pricing_type'],
                    'hourly_rate' => $serviceData['hourly_rate'] ?? null,
                    'one_time_price' => $serviceData['one_time_price'] ?? null,
                    'project_price' => $serviceData['project_price'] ?? null,
                    'monthly_price' => $serviceData['monthly_price'] ?? null,
                    'duration' => $serviceData['duration'],
                    'is_active' => $serviceData['is_active'],
                ]);
            }
        }

        $this->command->info('Services seeded successfully!');
    }
}
