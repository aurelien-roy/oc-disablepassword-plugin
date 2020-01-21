<?php namespace Tlokuus\DisabledPassword\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddPasswordUnsetField extends Migration
{
    public function up()
    {
        if(Schema::hasColumn('users', 'password_unset')){
            return;
        }

        Schema::table('users', function($table){
            $table->boolean('password_unset')->default(false);
        });
    }
    
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('password_unset');
        });

    }
}