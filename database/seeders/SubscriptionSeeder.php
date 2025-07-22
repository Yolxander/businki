<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing client IDs and user ID
        $clientIds = DB::table('clients')->pluck('id')->toArray();
        $userId = DB::table('users')->value('id');

        $subscriptions = [
            [
                'title' => 'Website Maintenance - Monthly',
                'client_id' => $clientIds[0] ?? null,
                'user_id' => $userId,
                'service_type' => 'Website Maintenance',
                'billing_cycle' => 'monthly',
                'amount' => 299.00,
                'description' => 'Monthly website maintenance including updates, security patches, and content updates',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 'active',
                'next_billing' => '2024-02-01',
                'total_billed' => 299.00,
                'payments_received' => 1,
                'billing_history' => json_encode([
                    [
                        'date' => '2024-01-01',
                        'amount' => 299.00,
                        'status' => 'paid'
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'SEO Services - Quarterly',
                'client_id' => $clientIds[1] ?? null,
                'user_id' => $userId,
                'service_type' => 'SEO Services',
                'billing_cycle' => 'quarterly',
                'amount' => 1500.00,
                'description' => 'Quarterly SEO optimization and reporting services',
                'start_date' => '2024-01-15',
                'end_date' => '2024-12-31',
                'status' => 'active',
                'next_billing' => '2024-04-15',
                'total_billed' => 1500.00,
                'payments_received' => 1,
                'billing_history' => json_encode([
                    [
                        'date' => '2024-01-15',
                        'amount' => 1500.00,
                        'status' => 'paid'
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cloud Hosting - Yearly',
                'client_id' => $clientIds[2] ?? null,
                'user_id' => $userId,
                'service_type' => 'Cloud Hosting',
                'billing_cycle' => 'yearly',
                'amount' => 2400.00,
                'description' => 'Annual cloud hosting and server management services',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 'paused',
                'next_billing' => '2025-01-01',
                'total_billed' => 2400.00,
                'payments_received' => 1,
                'billing_history' => json_encode([
                    [
                        'date' => '2024-01-01',
                        'amount' => 2400.00,
                        'status' => 'paid'
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Content Management - Monthly',
                'client_id' => $clientIds[3] ?? null,
                'user_id' => $userId,
                'service_type' => 'Content Management',
                'billing_cycle' => 'monthly',
                'amount' => 199.00,
                'description' => 'Monthly content updates and management services',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 'active',
                'next_billing' => '2024-02-01',
                'total_billed' => 199.00,
                'payments_received' => 1,
                'billing_history' => json_encode([
                    [
                        'date' => '2024-01-01',
                        'amount' => 199.00,
                        'status' => 'paid'
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('subscriptions')->insert($subscriptions);
    }
}
