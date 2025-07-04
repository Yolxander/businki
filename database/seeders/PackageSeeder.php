<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Profile;
use App\Models\PackageFeature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
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

        $packages = [
            [
                'name' => 'Starter Website Package',
                'description' => 'Perfect for small businesses getting started online with a professional web presence.',
                'type' => 'Starter',
                'price' => 800.00,
                'billing_cycle' => 'One-time',
                'is_active' => true,
                'features' => [
                    '3-page responsive website',
                    'Basic SEO setup',
                    'Contact form',
                    '1 round of revisions',
                    'Mobile-friendly design',
                    'Basic analytics setup',
                    'Social media integration',
                    '2 weeks delivery time'
                ]
            ],
            [
                'name' => 'Professional Website Package',
                'description' => 'Comprehensive website solution for growing businesses with advanced features.',
                'type' => 'Professional',
                'price' => 1500.00,
                'billing_cycle' => 'One-time',
                'is_active' => true,
                'features' => [
                    '5-8 page responsive website',
                    'Advanced SEO optimization',
                    'Blog setup and integration',
                    'E-commerce functionality',
                    'Payment gateway integration',
                    '3 rounds of revisions',
                    'Performance optimization',
                    'Security implementation',
                    'Content management system',
                    '4 weeks delivery time'
                ]
            ],
            [
                'name' => 'Premium Website Package',
                'description' => 'Enterprise-level website solution with custom functionality and premium features.',
                'type' => 'Premium',
                'price' => 3000.00,
                'billing_cycle' => 'One-time',
                'is_active' => true,
                'features' => [
                    '10+ page custom website',
                    'Advanced SEO and marketing tools',
                    'Custom functionality development',
                    'Multi-language support',
                    'Advanced analytics and reporting',
                    'API integration',
                    'Unlimited revisions',
                    'Priority support',
                    'Performance optimization',
                    'Security audit',
                    '6 weeks delivery time'
                ]
            ],
            [
                'name' => 'Monthly SEO Package',
                'description' => 'Ongoing SEO services to improve your website\'s search engine rankings.',
                'type' => 'Professional',
                'price' => 500.00,
                'billing_cycle' => 'Monthly',
                'is_active' => true,
                'features' => [
                    'Monthly keyword research',
                    'On-page SEO optimization',
                    'Content optimization',
                    'Technical SEO audit',
                    'Monthly performance reports',
                    'Google Analytics setup',
                    'Search Console monitoring',
                    'Competitor analysis',
                    'Link building strategies'
                ]
            ],
            [
                'name' => 'Social Media Management',
                'description' => 'Complete social media management for your business across all major platforms.',
                'type' => 'Starter',
                'price' => 300.00,
                'billing_cycle' => 'Monthly',
                'is_active' => true,
                'features' => [
                    '3 social media platforms',
                    '12 posts per month',
                    'Content creation',
                    'Community engagement',
                    'Monthly performance reports',
                    'Hashtag research',
                    'Basic graphic design',
                    'Response to comments'
                ]
            ],
            [
                'name' => 'E-commerce Development',
                'description' => 'Complete e-commerce solution with advanced features and payment processing.',
                'type' => 'Premium',
                'price' => 2500.00,
                'billing_cycle' => 'One-time',
                'is_active' => true,
                'features' => [
                    'Custom e-commerce website',
                    'Product catalog management',
                    'Shopping cart functionality',
                    'Payment gateway integration',
                    'Inventory management',
                    'Order processing system',
                    'Customer account portal',
                    'Shipping calculator',
                    'Tax calculation',
                    '8 weeks delivery time'
                ]
            ],
            [
                'name' => 'Brand Identity Package',
                'description' => 'Complete brand identity design including logo, colors, and brand guidelines.',
                'type' => 'Professional',
                'price' => 1200.00,
                'billing_cycle' => 'One-time',
                'is_active' => true,
                'features' => [
                    'Logo design (3 concepts)',
                    'Color palette development',
                    'Typography selection',
                    'Brand guidelines document',
                    'Business card design',
                    'Letterhead design',
                    'Social media templates',
                    '2 rounds of revisions',
                    'Source files included'
                ]
            ],
            [
                'name' => 'Content Marketing Package',
                'description' => 'Monthly content creation and marketing services to grow your online presence.',
                'type' => 'Starter',
                'price' => 400.00,
                'billing_cycle' => 'Monthly',
                'is_active' => true,
                'features' => [
                    '4 blog posts per month',
                    'SEO-optimized content',
                    'Social media content',
                    'Email newsletter content',
                    'Content calendar',
                    'Keyword research',
                    'Content performance tracking',
                    'Basic editing and proofreading'
                ]
            ],
            [
                'name' => 'Website Maintenance',
                'description' => 'Ongoing website maintenance and support to keep your site running smoothly.',
                'type' => 'Starter',
                'price' => 150.00,
                'billing_cycle' => 'Monthly',
                'is_active' => true,
                'features' => [
                    'Regular security updates',
                    'Plugin and theme updates',
                    'Daily backups',
                    'Performance monitoring',
                    'Uptime monitoring',
                    'Basic content updates',
                    'Security scanning',
                    'Monthly maintenance reports'
                ]
            ],
            [
                'name' => 'Custom Development Package',
                'description' => 'Custom web application development tailored to your specific business needs.',
                'type' => 'Custom',
                'price' => 5000.00,
                'billing_cycle' => 'One-time',
                'is_active' => true,
                'features' => [
                    'Custom web application',
                    'Database design',
                    'API development',
                    'User authentication',
                    'Admin dashboard',
                    'Custom integrations',
                    'Testing and quality assurance',
                    'Documentation',
                    'Training and support',
                    '12 weeks delivery time'
                ]
            ],
            [
                'name' => 'Digital Marketing Suite',
                'description' => 'Comprehensive digital marketing package including SEO, PPC, and social media.',
                'type' => 'Premium',
                'price' => 800.00,
                'billing_cycle' => 'Monthly',
                'is_active' => true,
                'features' => [
                    'Full SEO optimization',
                    'Google Ads management',
                    'Social media management',
                    'Content marketing',
                    'Email marketing campaigns',
                    'Analytics and reporting',
                    'Conversion optimization',
                    'Monthly strategy sessions',
                    'Priority support'
                ]
            ],
            [
                'name' => 'WordPress Maintenance',
                'description' => 'Specialized WordPress maintenance and optimization services.',
                'type' => 'Starter',
                'price' => 200.00,
                'billing_cycle' => 'Monthly',
                'is_active' => true,
                'features' => [
                    'WordPress core updates',
                    'Plugin and theme updates',
                    'Security monitoring',
                    'Performance optimization',
                    'Database optimization',
                    'Backup management',
                    'Malware scanning',
                    'Uptime monitoring',
                    'Emergency support'
                ]
            ]
        ];

        foreach ($profiles as $profile) {
            // Create 2-4 random packages for each profile
            $randomPackages = collect($packages)->random(rand(2, 4));

            foreach ($randomPackages as $packageData) {
                $package = Package::create([
                    'profile_id' => $profile->id,
                    'name' => $packageData['name'],
                    'description' => $packageData['description'],
                    'type' => $packageData['type'],
                    'price' => $packageData['price'],
                    'billing_cycle' => $packageData['billing_cycle'],
                    'is_active' => $packageData['is_active'],
                ]);

                // Create features for the package
                foreach ($packageData['features'] as $index => $feature) {
                    PackageFeature::create([
                        'package_id' => $package->id,
                        'feature' => $feature,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        $this->command->info('Packages seeded successfully!');
    }
}
