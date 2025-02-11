<?php

namespace Database\Seeders;

use App\Models\TopBar;
use Illuminate\Database\Seeder;

class TopBarsTableSeeder extends Seeder
{
    public function run()
    {
        TopBar::create([
            'content' => '🔥 پیشنهاد ویژه! دوره آموزشی لاراول با 50% تخفیف',
            'button_name' => 'مشاهده دوره',
            'link' => '/courses/laravel'
        ]);

        TopBar::create([
            'content' => '🎉 وبینار رایگان درباره هوش مصنوعی - چهارشنبه 25 مرداد',
            'button_name' => 'ثبت نام',
            'link' => '/webinars/ai'
        ]);
    }
}
