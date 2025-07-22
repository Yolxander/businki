<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');

        $clients = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@acme.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 King St W',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip_code' => 'M5H 1A1',
                'company_name' => 'Acme Corporation',
                'website' => 'www.acme.com',
                'description' => 'Client focused on residential property management.',
                'status' => 'active',
                'total_revenue' => 25000.00,
                'last_contact' => '2024-01-15',
                'rating' => 5,
                'contact_person' => 'John Smith',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah@techstart.com',
                'phone' => '+1 (555) 987-6543',
                'address' => '456 Queen St E',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip_code' => 'M5A 1T1',
                'company_name' => 'TechStart Solutions',
                'website' => 'www.techstart.com',
                'description' => 'Boutique hotel client for web and booking platform.',
                'status' => 'active',
                'total_revenue' => 18000.00,
                'last_contact' => '2024-01-20',
                'rating' => 4,
                'contact_person' => 'Sarah Johnson',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Chen',
                'email' => 'mike@innovatelab.com',
                'phone' => '+1 (555) 456-7890',
                'address' => '789 Bay St',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip_code' => 'M5G 2C3',
                'company_name' => 'InnovateLab',
                'website' => 'www.innovatelab.com',
                'description' => 'Innovative startup focused on AI solutions.',
                'status' => 'prospect',
                'total_revenue' => 0.00,
                'last_contact' => '2024-01-10',
                'rating' => 3,
                'contact_person' => 'Mike Chen',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily@retailplus.com',
                'phone' => '+1 (555) 789-0123',
                'address' => '321 Yonge St',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip_code' => 'M5B 2H1',
                'company_name' => 'RetailPlus',
                'website' => 'www.retailplus.com',
                'description' => 'Retail company looking for e-commerce solutions.',
                'status' => 'active',
                'total_revenue' => 12000.00,
                'last_contact' => '2024-01-25',
                'rating' => 5,
                'contact_person' => 'Emily Davis',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('clients')->insert($clients);

        // Create user-client relationships
        foreach ($clients as $client) {
            DB::table('user_client')->insert([
                'user_id' => $userId,
                'client_id' => DB::table('clients')->where('email', $client['email'])->value('id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
