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
     * Seed the application's database with actual event stand data.
     */
    public function run(): void
    {
        // 1. Admin Account (New Password)
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

        // 2. Real Cashier Accounts (8 Team Members)
        $teamMembers = [
            ['username' => 'farhad', 'name' => 'Farhad', 'email' => 'farhad@stand-pktos.local'],
            ['username' => 'natasya', 'name' => 'Natasya', 'email' => 'natasya@stand-pktos.local'],
            ['username' => 'naila', 'name' => 'Naila', 'email' => 'naila@stand-pktos.local'],
            ['username' => 'gita', 'name' => 'Gita', 'email' => 'gita@stand-pktos.local'],
            ['username' => 'azzilfa', 'name' => 'Azzilfa', 'email' => 'azzilfa@stand-pktos.local'],
            ['username' => 'rani', 'name' => 'Rani', 'email' => 'rani@stand-pktos.local'],
            ['username' => 'gustian', 'name' => 'Gustian', 'email' => 'gustian@stand-pktos.local'],
            ['username' => 'alya', 'name' => 'Alya', 'email' => 'alya@stand-pktos.local'],
        ];

        $usersByUsername = [];
        foreach ($teamMembers as $tm) {
            $user = User::updateOrCreate(
                ['username' => $tm['username']],
                [
                    'name' => $tm['name'],
                    'email' => $tm['email'],
                    'password' => Hash::make($tm['username'].'2026'),
                    'role' => 'cashier',
                    'is_active' => true,
                ]
            );
            $usersByUsername[$tm['username']] = $user;
        }

        // 3. Stand Menu & Flavors
        $menuItems = [
            // Qtella 5k
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

            // Krupuk Koin 2K
            [
                'code' => 'PRD-KOIN2-BAL',
                'name' => 'Krupuk Koin 2K - Balado',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 5,
            ],
            [
                'code' => 'PRD-KOIN2-BBQ',
                'name' => 'Krupuk Koin 2K - BBQ',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 6,
            ],
            [
                'code' => 'PRD-KOIN2-JAG',
                'name' => 'Krupuk Koin 2K - Jagung Bakar',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 7,
            ],
            [
                'code' => 'PRD-KOIN2-PDS',
                'name' => 'Krupuk Koin 2K - Extra Pedas',
                'category' => 'Makanan',
                'price' => 2000,
                'sort_order' => 8,
            ],

            // Krupuk Koin 3K
            [
                'code' => 'PRD-KOIN3-BAL',
                'name' => 'Krupuk Koin 3K - Balado',
                'category' => 'Makanan',
                'price' => 3000,
                'sort_order' => 9,
            ],
            [
                'code' => 'PRD-KOIN3-BBQ',
                'name' => 'Krupuk Koin 3K - BBQ',
                'category' => 'Makanan',
                'price' => 3000,
                'sort_order' => 10,
            ],
            [
                'code' => 'PRD-KOIN3-JAG',
                'name' => 'Krupuk Koin 3K - Jagung Bakar',
                'category' => 'Makanan',
                'price' => 3000,
                'sort_order' => 11,
            ],
            [
                'code' => 'PRD-KOIN3-PDS',
                'name' => 'Krupuk Koin 3K - Extra Pedas',
                'category' => 'Makanan',
                'price' => 3000,
                'sort_order' => 12,
            ],

            // Minuman
            [
                'code' => 'PRD-MOJITO',
                'name' => 'Mojito',
                'category' => 'Minuman',
                'price' => 5000,
                'sort_order' => 13,
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
                'shift_pagi' => ['farhad'],
                'shift_siang' => ['natasya', 'naila'],
            ],
            [
                'day_name' => 'Selasa',
                'offset' => 1,
                'status' => 'upcoming',
                'shift_pagi' => ['gita', 'azzilfa'],
                'shift_siang' => ['farhad', 'rani'],
            ],
            [
                'day_name' => 'Rabu',
                'offset' => 2,
                'status' => 'upcoming',
                'shift_pagi' => ['natasya', 'gustian'],
                'shift_siang' => ['gita', 'azzilfa'],
            ],
            [
                'day_name' => 'Kamis',
                'offset' => 3,
                'status' => 'upcoming',
                'shift_pagi' => ['naila', 'rani'],
                'shift_siang' => ['gustian', 'alya'],
            ],
            [
                'day_name' => 'Jumat (Penutupan)',
                'offset' => 4,
                'status' => 'upcoming',
                'shift_pagi' => ['farhad', 'alya'],
                'shift_siang' => ['natasya', 'rani'],
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
