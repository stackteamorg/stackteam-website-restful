<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            ['name' => 'آموزشی', 'slug' => 'educational'],
            ['name' => 'فناوری', 'slug' => 'technology'],
            ['name' => 'برنامه نویسی', 'slug' => 'programming'],
            ['name' => 'طراحی وب', 'slug' => 'web-design'],
            ['name' => 'هوش مصنوعی', 'slug' => 'ai'],
            ['name' => 'اخبار', 'slug' => 'news'],
            ['name' => 'سلامتی', 'slug' => 'health'],
            ['name' => 'گردشگری', 'slug' => 'tourism'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
