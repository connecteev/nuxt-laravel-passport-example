<?php

use App\Role;
use App\User;
// use App\Profile;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

// Call using command: php artisan db:seed --class=AdminUsersTableSeeder
class AdminUsersTableSeeder extends Seeder
{
    public function run(Faker $faker)
    {
        $adminRole = Role::where([
            // 'title' => 'Admin',
            'title' => 'User',
        ])->firstOrFail();

        factory(User::class, 1)
            ->create(
                [
                    'id'                => 1,
                    'email'             => env('ADMIN_EMAIL', 'admin@admin.com'),
                    // 'username'          => env('ADMIN_USERNAME', 'admin'),
                    'password'          => Hash::make(env('ADMIN_PASSWORD', 'password')),
                    // 'email_verified_at' => now(),
                    'remember_token'    => null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ],
            )
            ->each(function ($user) use ($faker, $adminRole) {
                //     // creating fake profile for each user
                //     factory(Profile::class, 1)->create([
                //         'user_id'       => $user,
                //         'full_name'     => env('ADMIN_FULLNAME', "Admin Account"),
                //         'bio_headline'  => env('ADMIN_BIO_HEADLINE', "Admin Account's Short Bio Headline"),
                //     ]);

                // populate the role_user table
                $user->roles()->sync($adminRole->id);
            });
    }
}
