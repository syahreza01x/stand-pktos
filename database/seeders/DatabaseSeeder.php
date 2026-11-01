<?php

namespace Database\Seeders;

use App\Models\EventDay;
use App\Models\Product;
use App\Models\Setting;
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

            // Shift 1: Shift Pagi (08:00 - 12:45)
            $isMondayOpen = ($sc['offset'] === 0);
            $s1Status = $isMondayOpen ? 'open' : 'scheduled';

            $shiftPagi = Shift::updateOrCreate(
                [
                    'event_day_id' => $eventDay->id,
                    'shift_number' => 'Shift 1',
                ],
                [
                    'name' => 'Shift Pagi (08:00 - 12:45)',
                    'start_time' => '08:00:00',
                    'end_time' => '12:45:00',
                    'status' => $s1Status,
                    'opened_at' => $isMondayOpen ? Carbon::now() : null,
                    'opened_by' => $isMondayOpen ? $admin->id : null,
                ]
            );

            // Shift 2: Shift Siang (13:15 - 17:15)
            $shiftSiang = Shift::updateOrCreate(
                [
                    'event_day_id' => $eventDay->id,
                    'shift_number' => 'Shift 2',
                ],
                [
                    'name' => 'Shift Siang (13:15 - 17:15)',
                    'start_time' => '13:15:00',
                    'end_time' => '17:15:00',
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

        // 5. ShopeePay Merchant (Shopee Partner) Initial Settings
        Setting::updateOrCreate(
            ['key' => 'shopeepay_cookie'],
            [
                'value' => 'SPC_F=kpLhv3xlWqaPOPpi2V7pw1vkb7jviwR3; REC_T_ID=de368605-323a-11f1-bdbf-b28af8d2aaaf; _ga=GA1.1.1107439150.1775536379; language=id; SPC_CLIENTID=a3BMaHYzeGxXcWFQfmspsirriwdjlnid; SPC_EC=-; _gcl_au=1.1.732674978.1792399734; _med=refer; _fbp=fb.2.1792399735967.26103651953017647; SPC_ST=q30Vyb3xH/xWwqoJZNZS3kj1hR/28QEJ7fVgL8xd/fMUUIOvCILWpxL+riFwtX4rdwPJAFl+tMtkOJb7IZics13HysKR2W2oqYami4ixaI8xx8YWY456bLkhUIwJpTByGxaKQFe3xHmPhTN7kucTVGNMTill8BItj/RWRzmiJ/M2vrEsf0+6fhcztd5G8lhjggUX3zjqs4qMyTbMbLK/RasXPUGAdgveu7gMr7QnJpU=.ACKd7jBGEqAnyvk4SVNy+59rdQssNH5Ru7QeONPnV59H; SPC_U=16147324838; SPC_R_T_ID=fjQDdZbtDT7Fuy09o4HkKDnznW9Bf9Yb7HIBe4OVKiPoYTt9b1JoTTzG6wG6Ln93fOjY0hDTiadi/ZOHY0cXq0mDyGB9Plbqgd72OHpao+YLQVWPC48rE+ePFzZydbG8aSpwLwmWdFSFgl6CtT5INjrnZbwRzc+i6zTjegSI5jI=; SPC_R_T_IV=eFdvTWNsZlp5Mk4zbklReA==; SPC_T_ID=fjQDdZbtDT7Fuy09o4HkKDnznW9Bf9Yb7HIBe4OVKiPoYTt9b1JoTTzG6wG6Ln93fOjY0hDTiadi/ZOHY0cXq0mDyGB9Plbqgd72OHpao+YLQVWPC48rE+ePFzZydbG8aSpwLwmWdFSFgl6CtT5INjrnZbwRzc+i6zTjegSI5jI=; SPC_T_IV=eFdvTWNsZlp5Mk4zbklReA==; _ga_SW6D8G0HXK=GS2.1.s1792399736$o4$g1$t1792400048$j1$l1$h1753050467; __shopee_partner_website_x_token_live=eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyaWQiOiIxNDkzNzEwMzg3NSIsInJlZ2lvbiI6IklEIiwiYnVzaW5lc3NJZCI6MSwidG9rZW4iOiJCOkVINlhTRTdIVy8rc0F5bmUrWmRmd3FQR0VUS3pvbDFSblZ6bE1lcjRCMG9rc2JVWGEvSU5RejczYThkeGt3eEtoalk1QWxWandKNzNqUndwbmRKRU1WOTg3UHNPTmFMc29HT1AyVnc4ZVF1am83RVV5T1JLZGo0SnRaZ1Y5dU5FVXFvdzNlVEp5bmtyQ1BLc3ZmOEpqZ1gxVitCV1FkTmsxWEhKWXduS0FFcit2aGNtUFpyOTVyTE5RYWFsZmNOT2ttYXpmYjdPbjVyc0U4N0EzNzRVLzNkYk1VS3Jva1piaHBReHhwST0iLCJlcnJvck1zZyI6IiIsImVycm9yQ29kZSI6MCwiaWF0IjoxNzkzNTM1NzUyLCJleHAiOjE4Nzk5MzU3NTJ9.jWcQBBUjbVV7eJ-nvU2qMTRi20mWBaVsBd3HZTgYmM-p9Vwne_z8SHHtJH0SnbNGo95dyeTNB4VuFnW6K9sdsik1D_TCLmH0W-wHzEkaBammilPwUO1PNGCQkGIkCsuUi53PwNz_qboM0xg_GiacYRIuFdId5EvftXpurWiz6H8',
                'description' => 'Session Cookie ShopeePay Merchant (Shopee Partner)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'shopeepay_token'],
            [
                'value' => 'B:EH6XSE7HW/+sAyne+ZdfwqPGETKzol1RnVzlMer4B0oksbUXa/INQz73a8dxkwxKhjY5AlVjwJ73jRwpndJEMV987PsONaLsoGOP2Vw8eQujo7EUyORKdj4JtZgV9uNEUqow3eTJynkrCPKsvf8JjgX1V+BWQdNk1XHJYwnKAEr+vhcmPZr95rLNQaalfcNOkmazfb7On5rsE87A374U/3dbMUKrokZbhpQxxpI=',
                'description' => 'Metadata Token ShopeePay Merchant (Shopee Partner)',
            ]
        );
    }
}
