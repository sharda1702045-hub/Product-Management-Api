<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a demo user for the Web Login Flow
        $webUser = User::updateOrCreate(
            ['email' => 'web-demo@example.com'],
            [
                'name' => 'Web Demo User',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Create a demo user for the API Flow
        $apiUser = User::updateOrCreate(
            ['email' => 'api-demo@example.com'],
            [
                'name' => 'API Demo User',
                'password' => Hash::make('password123'),
            ]
        );

        // Clear any existing tokens for these users to prevent bloating
        $webUser->tokens()->delete();
        $apiUser->tokens()->delete();

        // Create a fixed/known token structure for API testing if needed,
        // or generate new ones and print them.
        $apiToken = $apiUser->createToken('api_flow_token')->plainTextToken;

        // 3. Create high-quality realistic products for a premium dashboard look
        $products = [
            [
                'name' => 'iPhone 15 Pro Max',
                'description' => 'Apple flagship smartphone with titanium design, 6.7-inch Super Retina XDR display, and A17 Pro chip.',
                'price' => 1199.00,
                'quantity' => 45,
            ],
            [
                'name' => 'MacBook Pro 16-inch',
                'description' => 'Apple M3 Pro chip with 12‑core CPU and 18‑core GPU, 18GB Unified Memory, 512GB SSD, Space Black.',
                'price' => 2499.00,
                'quantity' => 20,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Industry-leading noise-canceling wireless overhead headphones with Alexa and Google Assistant integration.',
                'price' => 398.00,
                'quantity' => 85,
            ],
            [
                'name' => 'iPad Pro 12.9-inch',
                'description' => 'M2 processor, Liquid Retina XDR screen, Wi-Fi 6E, 256GB storage, compatible with Apple Pencil (2nd Gen).',
                'price' => 1099.00,
                'quantity' => 30,
            ],
            [
                'name' => 'Dell XPS 15 Laptop',
                'description' => 'High-performance laptop featuring 13th Gen Intel Core i9, 32GB RAM, 1TB SSD, and NVIDIA RTX 4060 graphics.',
                'price' => 1899.99,
                'quantity' => 15,
            ],
            [
                'name' => 'Logitech MX Master 3S',
                'description' => 'Ergonomic wireless mouse with ultra-quiet clicks, 8K DPI tracking, and electromagnetic MagSpeed wheel.',
                'price' => 99.99,
                'quantity' => 120,
            ],
            [
                'name' => 'Keychron Q1 Pro Keyboard',
                'description' => 'Premium fully assembled wireless custom mechanical keyboard with aluminum body and hot-swappable switches.',
                'price' => 199.99,
                'quantity' => 60,
            ],
            [
                'name' => 'Apple Watch Ultra 2',
                'description' => 'Rugged smartwatch for athletes and outdoor adventurers. Dual-frequency GPS, 36-hour battery, and titanium case.',
                'price' => 799.00,
                'quantity' => 25,
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Premium Android phone with titanium frame, built-in S Pen, 200MP camera system, and integrated Galaxy AI.',
                'price' => 1299.99,
                'quantity' => 40,
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'description' => 'Handheld gaming console with 7-inch OLED screen, 64GB storage, enhanced audio, and wider adjustable stand.',
                'price' => 349.99,
                'quantity' => 75,
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }

        // Print details to CLI
        $this->command->info('==================================================');
        $this->command->info('FLOW SEEDER COMPLETED SUCCESSFULLY!');
        $this->command->info('==================================================');
        $this->command->info('WEB LOGIN FLOW USER:');
        $this->command->info('  Email:    web-demo@example.com');
        $this->command->info('  Password: password123');
        $this->command->info('--------------------------------------------------');
        $this->command->info('API FLOW USER:');
        $this->command->info('  Email:    api-demo@example.com');
        $this->command->info('  Password: password123');
        $this->command->info('  Pre-Generated Access Token:');
        $this->command->warn('  '.$apiToken);
        $this->command->info('==================================================');
    }
}
