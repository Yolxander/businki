<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing client IDs and user ID
        $clientIds = DB::table('clients')->pluck('id')->toArray();
        $userId = DB::table('users')->value('id');

        $proposals = [
            [
                'title' => 'Website Redesign Proposal',
                'description' => 'Complete website redesign with modern UI/UX and improved functionality',
                'scope' => 'Full website redesign including homepage, about, services, and contact pages',
                'deliverables' => json_encode([
                    'Responsive website design',
                    'Content management system',
                    'SEO optimization',
                    'Analytics integration',
                    'Training and documentation'
                ]),
                'timeline' => json_encode([
                    'Discovery and Planning: 1 week',
                    'Design Phase: 2 weeks',
                    'Development: 3 weeks',
                    'Testing and Launch: 1 week'
                ]),
                'price' => 15000.00,
                'status' => 'accepted',
                'valid_until' => now()->addDays(30),
                'version' => '1.0',
                'terms_conditions' => json_encode([
                    'Payment: 50% upfront, 50% upon completion',
                    'Revisions: 2 rounds of revisions included',
                    'Warranty: 30-day bug fix warranty'
                ]),
                'payment_terms' => json_encode([
                    'Deposit: $7,500 (50%)',
                    'Final Payment: $7,500 upon completion',
                    'Due Date: 30 days from acceptance'
                ]),
                'client_id' => $clientIds[0] ?? null,
                'intake_response_id' => null, // Proposals can exist without intake responses
                'user_id' => $userId,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(5),
            ],
            [
                'title' => 'Brand Identity Package',
                'description' => 'Complete brand identity development including logo, colors, and guidelines',
                'scope' => 'Brand identity package with logo design, color palette, and brand guidelines',
                'deliverables' => json_encode([
                    'Primary and secondary logo designs',
                    'Color palette and typography',
                    'Brand guidelines document',
                    'Business card and letterhead designs',
                    'Social media templates'
                ]),
                'timeline' => json_encode([
                    'Discovery: 3 days',
                    'Concept Development: 1 week',
                    'Refinement: 1 week',
                    'Finalization: 3 days'
                ]),
                'price' => 8000.00,
                'status' => 'sent',
                'valid_until' => now()->addDays(21),
                'version' => '1.0',
                'terms_conditions' => json_encode([
                    'Payment: 50% upfront, 50% upon completion',
                    'Revisions: 3 rounds of revisions included',
                    'File formats: Vector and raster formats provided'
                ]),
                'payment_terms' => json_encode([
                    'Deposit: $4,000 (50%)',
                    'Final Payment: $4,000 upon completion',
                    'Due Date: 21 days from acceptance'
                ]),
                'client_id' => $clientIds[1] ?? null,
                'intake_response_id' => null,
                'user_id' => $userId,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'title' => 'Mobile App Development',
                'description' => 'Cross-platform mobile application for iOS and Android',
                'scope' => 'Full mobile app development with backend API and admin dashboard',
                'deliverables' => json_encode([
                    'iOS and Android mobile apps',
                    'Backend API development',
                    'Admin dashboard',
                    'App store submission',
                    'User documentation'
                ]),
                'timeline' => json_encode([
                    'Planning and Design: 2 weeks',
                    'Development: 8 weeks',
                    'Testing: 2 weeks',
                    'App Store Submission: 1 week'
                ]),
                'price' => 25000.00,
                'status' => 'draft',
                'valid_until' => now()->addDays(45),
                'version' => '1.0',
                'terms_conditions' => json_encode([
                    'Payment: 30% upfront, 40% at milestone, 30% upon completion',
                    'Maintenance: 3 months post-launch support included',
                    'Updates: Minor updates included for 6 months'
                ]),
                'payment_terms' => json_encode([
                    'Deposit: $7,500 (30%)',
                    'Milestone Payment: $10,000 (40%)',
                    'Final Payment: $7,500 (30%) upon completion'
                ]),
                'client_id' => $clientIds[2] ?? null,
                'intake_response_id' => null,
                'user_id' => $userId,
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ]
        ];

        DB::table('proposals')->insert($proposals);
    }
}
