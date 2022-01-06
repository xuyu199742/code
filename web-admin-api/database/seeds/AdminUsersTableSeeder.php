<?php

use Illuminate\Database\Seeder;

class AdminUsersTableSeeder extends Seeder
{
    public function __construct()
    {
        if (DB::getDriverName() == 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT admin_users ON;');
        }
    }

    public function __destruct()
    {
        if (DB::getDriverName() == 'sqlsrv') {
            DB::unprepared('SET IDENTITY_INSERT admin_users OFF;');
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_users')->delete();
        DB::table('admin_users')->insert(array(
                0 =>
                    array(
                        'id'             => 1,
                        'username'       => 'ldhy',
                        'email'          => 'ldhy@admin.com',
                        'mobile'         => '18888888888',
                        'sex'            => 1,
                        'password'       => bcrypt('l8D2h5Y2@Offel'),
                        'remember_token' => '',
                        'created_at'     => date('Y-m-d H:i:s', time()),
                        'updated_at'     => date('Y-m-d H:i:s', time()),
                        'deleted_at'     => null,
                    ),
                1 =>
                    array(
                        'id'             => 2,
                        'username'       => 'admin',
                        'email'          => 'guest@admin.com',
                        'mobile'         => '17777777777',
                        'sex'            => 1,
                        'password'       => bcrypt('11111111'),
                        'remember_token' => '',
                        'created_at'     => date('Y-m-d H:i:s', time()),
                        'updated_at'     => date('Y-m-d H:i:s', time()),
                        'deleted_at'     => null,
                    ),
            )
        );
    }
}
