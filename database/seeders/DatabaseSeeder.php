<?php

namespace Database\Seeders;

use App\Models\Bike;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sepedakuy.test'],
            [
                'name' => 'Admin SepedaKuy',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081234567890',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@sepedakuy.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'phone' => '089876543210',
            ]
        );

        $paymentMethods = [
            [
                'name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'SepedaKuy Official',
                'is_active' => true,
            ],
            [
                'name' => 'Mandiri',
                'account_number' => '9876543210123',
                'account_holder' => 'SepedaKuy Official',
                'is_active' => true,
            ],
            [
                'name' => 'DANA',
                'account_number' => '081234567890',
                'account_holder' => 'SepedaKuy Wallet',
                'is_active' => true,
            ],
            [
                'name' => 'GoPay',
                'account_number' => '081234567890',
                'account_holder' => 'SepedaKuy Wallet',
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                [
                    'name' => $method['name'],
                    'account_number' => $method['account_number'],
                ],
                $method
            );
        }

        $bikes = [
            [
                'code' => 'MTB-01',
                'name' => 'Polygon Cascade MTB',
                'type' => 'MTB',
                'price_per_hour' => 30000,
                'stock_total' => 5,
                'stock_available' => 5,
                'status' => 'available',
                'image' => null,
                'description' => 'Sepeda gunung tangguh untuk jalur menanjak, paving, dan semi-offroad.',
            ],
            [
                'code' => 'CTY-01',
                'name' => 'Classic City Cruiser',
                'type' => 'City Bike',
                'price_per_hour' => 20000,
                'stock_total' => 4,
                'stock_available' => 4,
                'status' => 'available',
                'image' => null,
                'description' => 'Nyaman untuk jalan santai di area kota, taman, atau jalur datar.',
            ],
            [
                'code' => 'FLD-01',
                'name' => 'Urban Folding Bike',
                'type' => 'Folding Bike',
                'price_per_hour' => 25000,
                'stock_total' => 3,
                'stock_available' => 3,
                'status' => 'available',
                'image' => null,
                'description' => 'Ringkas, ringan, dan lincah untuk perjalanan jarak dekat.',
            ],
            [
                'code' => 'EBK-01',
                'name' => 'City E-Bike Assist',
                'type' => 'E-Bike',
                'price_per_hour' => 45000,
                'stock_total' => 2,
                'stock_available' => 2,
                'status' => 'available',
                'image' => null,
                'description' => 'Dilengkapi motor listrik bantu agar kayuhan lebih ringan di tanjakan.',
            ],
        ];

        foreach ($bikes as $bike) {
            Bike::updateOrCreate(
                ['code' => $bike['code']],
                $bike
            );
        }
    }
}
