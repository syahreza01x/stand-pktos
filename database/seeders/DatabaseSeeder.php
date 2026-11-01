<?php

namespace Database\Seeders;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Koordinator Stand (Admin)',
                'email' => 'admin@stand-pktos.local',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2. Create Cashiers
        $cashiersData = [
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'email' => 'budi@stand-pktos.local',
            ],
            [
                'name' => 'Siti Rahma',
                'username' => 'siti',
                'email' => 'siti@stand-pktos.local',
            ],
            [
                'name' => 'Andi Pratama',
                'username' => 'andi',
                'email' => 'andi@stand-pktos.local',
            ],
            [
                'name' => 'Dewi Lestari',
                'username' => 'dewi',
                'email' => 'dewi@stand-pktos.local',
            ],
        ];

        $cashierModels = [];
        foreach ($cashiersData as $c) {
            $cashierModels[] = User::firstOrCreate(
                ['username' => $c['username']],
                [
                    'name' => $c['name'],
                    'email' => $c['email'],
                    'password' => Hash::make('password123'),
                    'role' => 'cashier',
                    'is_active' => true,
                ]
            );
        }

        // 3. Create 6 Core Products
        $products = [
            [
                'code' => 'PRD-BOBA',
                'name' => 'Boba Brown Sugar',
                'category' => 'Minuman',
                'price' => 10000,
                'sort_order' => 1,
            ],
            [
                'code' => 'PRD-ROTI',
                'name' => 'Roti Panggang Cokelat',
                'category' => 'Makanan',
                'price' => 5000,
                'sort_order' => 2,
            ],
            [
                'code' => 'PRD-ESTEH',
                'name' => 'Es Teh Manis Jumbo',
                'category' => 'Minuman',
                'price' => 4000,
                'sort_order' => 3,
            ],
            [
                'code' => 'PRD-KOPI',
                'name' => 'Kopi Susu Gula Aren',
                'category' => 'Minuman',
                'price' => 8000,
                'sort_order' => 4,
            ],
            [
                'code' => 'PRD-SIOMAY',
                'name' => 'Siomay Goreng Crispy',
                'category' => 'Makanan',
                'price' => 6000,
                'sort_order' => 5,
            ],
            [
                'code' => 'PRD-AIRMIN',
                'name' => 'Air Mineral Dingin',
                'category' => 'Minuman',
                'price' => 3000,
                'sort_order' => 6,
            ],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'price' => $p['price'],
                    'sort_order' => $p['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Create 5 Event Days (Senin - Jumat)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $daysConfig = [
            ['day' => 'Senin', 'offset' => 0, 'status' => 'active'],
            ['day' => 'Selasa', 'offset' => 1, 'status' => 'upcoming'],
            ['day' => 'Rabu', 'offset' => 2, 'status' => 'upcoming'],
            ['day' => 'Kamis', 'offset' => 3, 'status' => 'upcoming'],
            ['day' => 'Jumat', 'offset' => 4, 'status' => 'upcoming'],
        ];

        foreach ($daysConfig as $dc) {
            $date = (clone $startOfWeek)->addDays($dc['offset'])->format('Y-m-d');
            $eventDay = EventDay::firstOrCreate(
                ['event_date' => $date],
                [
                    'day_name' => $dc['day'],
                    'status' => $dc['status'],
                    'notes' => 'Penjualan Hari '.$dc['day'],
                ]
            );

            // Shift 1
            $s1Status = ($dc['day'] === 'Senin') ? 'open' : 'scheduled';
            $s1 = Shift::firstOrCreate(
                [
                    'event_day_id' => $eventDay->id,
                    'shift_number' => 'Shift 1',
                ],
                [
                    'name' => 'Shift Pagi (08:00 - 12:30)',
                    'start_time' => '08:00:00',
                    'end_time' => '12:30:00',
                    'status' => $s1Status,
                    'opened_at' => ($s1Status === 'open') ? Carbon::now() : null,
                    'opened_by' => ($s1Status === 'open') ? $admin->id : null,
                ]
            );

            // Shift 2
            $s2 = Shift::firstOrCreate(
                [
                    'event_day_id' => $eventDay->id,
                    'shift_number' => 'Shift 2',
                ],
                [
                    'name' => 'Shift Sore (12:30 - 17:00)',
                    'start_time' => '12:30:00',
                    'end_time' => '17:00:00',
                    'status' => 'scheduled',
                ]
            );

            // Assign cashiers to shifts
            // Shift 1 gets Budi and Siti
            ShiftAssignment::firstOrCreate([
                'shift_id' => $s1->id,
                'user_id' => $cashierModels[0]->id,
            ]);
            ShiftAssignment::firstOrCreate([
                'shift_id' => $s1->id,
                'user_id' => $cashierModels[1]->id,
            ]);

            // Shift 2 gets Andi and Dewi
            ShiftAssignment::firstOrCreate([
                'shift_id' => $s2->id,
                'user_id' => $cashierModels[2]->id,
            ]);
            ShiftAssignment::firstOrCreate([
                'shift_id' => $s2->id,
                'user_id' => $cashierModels[3]->id,
            ]);
        }
    }
}
