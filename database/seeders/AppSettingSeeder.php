<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::set('app_name', 'ArthaLedger');
        AppSetting::set('base_currency', 'IDR');
    }
}
