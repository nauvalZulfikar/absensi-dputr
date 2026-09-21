<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Profile;
use App\Models\Role;
use App\Models\RoleHasUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'superadmin'
        ]);

        Role::create([
            'name' => 'user'
        ]);

        Role::create([
            'name' => 'admin'
        ]);

        Role::create([
            'name' => 'user_admin',
        ]);

        $user = User::create([
            'name' => 'admin',
            'userName' => 'admin',
            'email' => 'admin@mail.com',
            'password' =>  bcrypt('123456'),
        ]);

        Profile::create([
            'userId' => $user->id,
            'name' => 'admin',
        ]);

        RoleHasUser::create([
            'userId' => $user->id,
            'roleId' => 1,
        ]);

        $user = User::create([
            'name' => 'user',
            'userName' => 'user',
            'email' => 'user@mail.com',
            'password' =>  bcrypt('123456'),
        ]);

        Profile::create([
            'userId' => $user->id,
            'name' => 'user',
        ]);

        RoleHasUser::create([
            'userId' => $user->id,
            'roleId' => 2,
        ]);
    }
}
