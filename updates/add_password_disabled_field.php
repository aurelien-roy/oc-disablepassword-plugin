<?php namespace Tlokuus\DisabledPassword\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddPasswordDisabledField extends Migration
{
    public function up()
    {
        if(Schema::hasColumn('users', 'tlokuus_disablepassword_is_disabled')){
            return;
        }

        Schema::table('users', function($table){
            $table->boolean('tlokuus_disablepassword_is_disabled')->default(false);
        });
    }
    
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('tlokuus_disablepassword_is_disabled');
        });

    }
}