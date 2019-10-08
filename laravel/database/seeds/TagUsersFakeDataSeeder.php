<?php

use Illuminate\Database\Seeder;

use App\User;
use App\Tag;
use Faker\Generator as Faker;

// Call using command: php artisan db:seed --class=TagUsersFakeDataSeeder
class TagUsersFakeDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        echo "Seeding Tag_Users data... \n";

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // use Faker to create random tags
        //factory(Tag::class, 20)->create();

        /*
         * Populate the pivot tables:
         * tag_user
         */
        $users = User::all();
        Tag::all()->each(function ($tag) use ($users) {
            $tag->users()->attach(
                // attach between 0 to 4 users per tag
                $users->random(rand(0, 4))->pluck('id')->toArray()
            );
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
