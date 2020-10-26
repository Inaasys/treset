<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')->insert(array(
            'name' => 'OSBALDO ANZALDO SEGURA',
            'email' => 'osbaldo.anzaldo@utpcamiones.com.mx',
            'email_verified_at' => NULL,
            'password' => '$2y$10$tmiuo81kfuaPzH1Ghp8MgOljevMPEY2xWvTKASIKcEad6QvmscDlC',
            'remember_token' => NULL,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s'),
            'user'=> 'OSBALDO',
            'role_id' => 1,
            'status' => 'ALTA',
            'first_login' => 1
        ));
        
        DB::table('users')->insert(array(
            'name' => 'FABIOLA LOZA',
            'email' => 'fabiola.loza@volvobajio.com',
            'email_verified_at' => NULL,
            'password' => '$2y$10$vqvte8Cf8bcn2FIj19weauBWBicCQvzPwEONxgOAlBcXFa8O69Zpu',
            'remember_token' => NULL,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s'),
            'user'=> 'FABIOLA',
            'role_id' => 1,
            'status' => 'ALTA',
            'first_login' => 0
        ));

        DB::table('users')->insert(array(
            'name' => 'VERONICA JAIMEZ',
            'email' => 'veronica.jaimez@utpcamiones.com.mx',
            'email_verified_at' => NULL,
            'password' => '$2y$10$VyagL4kzWhuzR0VrPCE.qOy/eugjBfm4g6/rRpBClB8GCVl94uKa.',
            'remember_token' => NULL,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s'),
            'user'=> 'VERONICA',
            'role_id' => 1,
            'status' => 'ALTA',
            'first_login' => 1
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
