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
        // 1. Admin Master Account
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Koordinator Stand (Admin)',
                'email' => 'admin@stand-pktos.local',
                'password' => Hash::make('adminpktos2026'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2. Akun Kasir Berbasis Shift (Shift Accounts)
        $shiftAccounts = [
            ['username' => 'seninpagi', 'name' => 'Petugas Shift Pagi (Senin)', 'email' => 'seninpagi@stand-pktos.local'],
            ['username' => 'seninsiang', 'name' => 'Petugas Shift Siang (Senin)', 'email' => 'seninsiang@stand-pktos.local'],
            ['username' => 'selasapagi', 'name' => 'Petugas Shift Pagi (Selasa)', 'email' => 'selasapagi@stand-pktos.local'],
            ['username' => 'selasasiang', 'name' => 'Petugas Shift Siang (Selasa)', 'email' => 'selasasiang@stand-pktos.local'],
            ['username' => 'rabupagi', 'name' => 'Petugas Shift Pagi (Rabu)', 'email' => 'rabupagi@stand-pktos.local'],
            ['username' => 'rabusiang', 'name' => 'Petugas Shift Siang (Rabu)', 'email' => 'rabusiang@stand-pktos.local'],
            ['username' => 'kamispagi', 'name' => 'Petugas Shift Pagi (Kamis)', 'email' => 'kamispagi@stand-pktos.local'],
            ['username' => 'kamissiang', 'name' => 'Petugas Shift Siang (Kamis)', 'email' => 'kamissiang@stand-pktos.local'],
            ['username' => 'jumatpagi', 'name' => 'Petugas Shift Pagi (Jumat)', 'email' => 'jumatpagi@stand-pktos.local'],
            ['username' => 'jumatsiang', 'name' => 'Petugas Shift Siang (Jumat)', 'email' => 'jumatsiang@stand-pktos.local'],
        ];

        $usersByUsername = [];
        foreach ($shiftAccounts as $acc) {
            $user = User::updateOrCreate(
                ['username' => $acc['username']],
                [
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'password' => Hash::make($acc['username'].'2026'),
                    'role' => 'cashier',
                    'is_active' => true,
                ]
            );
            $usersByUsername[$acc['username']] = $user;
        }

        // 3. Menu Stand PKTOS (Makanan & Minuman)
        $menuItems = [
            // Qtella (Makanan - 4 varian rasa)
            [
                'code' => 'PRD-QTL-BAL',
                'name' => 'Qtella - Balado',
                'category' => 'Makanan',
                'price' => 5000,
                'sort_order' => 1,
            ],
            [
                'code' => 'PRD-QTL-BBQ',
                'name' => 'Qtella - BBQ',
                'category' => 'Makanan',
                'price' => 5000,
                'sort_order' => 2,
            ],
            [
                'code' => 'PRD-QTL-JAG',
                'name' => 'Qtella - Jagung Bakar',
                'category' => 'Makanan',
                'price' => 5000,
                'sort_order' => 3,
            ],
            [
                'code' => 'PRD-QTL-PDS',
                'name' => 'Qtella - Extra Pedas',
                'category' => 'Makanan',
                'price' => 5000,
                'sort_order' => 4,
            ],

            // Kerupuk Koin (Makanan - 4 varian rasa)
            [
                'code' => 'PRD-KOIN-BAL',
                'name' => 'Kerupuk Koin - Balado',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 5,
            ],
            [
                'code' => 'PRD-KOIN-BBQ',
                'name' => 'Kerupuk Koin - BBQ',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 6,
            ],
            [
                'code' => 'PRD-KOIN-JAG',
                'name' => 'Kerupuk Koin - Jagung Bakar',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 7,
            ],
            [
                'code' => 'PRD-KOIN-PDS',
                'name' => 'Kerupuk Koin - Extra Pedas',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 8,
            ],

            // Minuman
            [
                'code' => 'PRD-MOJITO',
                'name' => 'Mojito',
                'category' => 'Minuman',
                'price' => 5000,
                'sort_order' => 9,
            ],
        ];

        foreach ($menuItems as $item) {
            Product::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'price' => $item['price'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Stand Event Schedule & Team Assignments
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $scheduleConfig = [
            [
                'day_name' => 'Senin (Pembukaan)',
                'offset' => 0,
                'status' => 'active',
                'shift_pagi' => ['seninpagi'],
                'shift_siang' => ['seninsiang'],
            ],
            [
                'day_name' => 'Selasa',
                'offset' => 1,
                'status' => 'upcoming',
                'shift_pagi' => ['selasapagi'],
                'shift_siang' => ['selasasiang'],
            ],
            [
                'day_name' => 'Rabu',
                'offset' => 2,
                'status' => 'upcoming',
                'shift_pagi' => ['rabupagi'],
                'shift_siang' => ['rabusiang'],
            ],
            [
                'day_name' => 'Kamis',
                'offset' => 3,
                'status' => 'upcoming',
                'shift_pagi' => ['kamispagi'],
                'shift_siang' => ['kamissiang'],
            ],
            [
                'day_name' => 'Jumat (Penutupan)',
                'offset' => 4,
                'status' => 'upcoming',
                'shift_pagi' => ['jumatpagi'],
                'shift_siang' => ['jumatsiang'],
            ],
        ];

        foreach ($scheduleConfig as $sc) {
            $date = (clone $startOfWeek)->addDays($sc['offset'])->format('Y-m-d');

            $eventDay = EventDay::updateOrCreate(
                ['event_date' => $date],
                [
                    'day_name' => $sc['day_name'],
                    'status' => $sc['status'],
                    'notes' => 'Hari Penjualan Stand: '.$sc['day_name'],
                ]
            );

            // Shift 1: Shift Pagi (08:00 - 12:30)
            $isMondayOpen = ($sc['offset'] === 0);
            $s1Status = $isMondayOpen ? 'open' : 'scheduled';

            $shiftPagi = Shift::updateOrCreate(
                [
                    'event_day_id' => $eventDay->id,
                    'shift_number' => 'Shift 1',
                ],
                [
                    'name' => 'Shift Pagi (08:00 - 12:30)',
                    'start_time' => '08:00:00',
                    'end_time' => '12:30:00',
                    'status' => $s1Status,
                    'opened_at' => $isMondayOpen ? Carbon::now() : null,
                    'opened_by' => $isMondayOpen ? $admin->id : null,
                ]
            );

            // Shift 2: Shift Siang (12:30 - 17:00)
            $shiftSiang = Shift::updateOrCreate(
                [
                    'event_day_id' => $eventDay->id,
                    'shift_number' => 'Shift 2',
                ],
                [
                    'name' => 'Shift Siang (12:30 - 17:00)',
                    'start_time' => '12:30:00',
                    'end_time' => '17:00:00',
                    'status' => 'scheduled',
                ]
            );

            // Clear old assignments for these shifts
            ShiftAssignment::whereIn('shift_id', [$shiftPagi->id, $shiftSiang->id])->delete();

            // Assign Shift Pagi
            foreach ($sc['shift_pagi'] as $uname) {
                if (isset($usersByUsername[$uname])) {
                    ShiftAssignment::create([
                        'shift_id' => $shiftPagi->id,
                        'user_id' => $usersByUsername[$uname]->id,
                    ]);
                }
            }

            // Assign Shift Siang
            foreach ($sc['shift_siang'] as $uname) {
                if (isset($usersByUsername[$uname])) {
                    ShiftAssignment::create([
                        'shift_id' => $shiftSiang->id,
                        'user_id' => $usersByUsername[$uname]->id,
                    ]);
                }
            }
        }
    }
}
