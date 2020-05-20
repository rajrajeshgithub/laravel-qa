<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        //factory(App\Question::class,10)->create();
        $this->call([
            UsersQuestionsAnswersTableSeeder::class,
            FavoritesTableSeeder::class
        ]);
    }
}
