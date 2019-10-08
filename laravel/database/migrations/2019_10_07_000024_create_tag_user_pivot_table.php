<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagUserPivotTable extends Migration
{
    public function up()
    {
        Schema::create('tag_user', function (Blueprint $table) {
            $table->unsignedInteger('tag_id');

            $table->foreign('tag_id', 'tag_id_fk_370395')->references('id')->on('tags')->onDelete('cascade');

            $table->unsignedInteger('user_id');

            $table->foreign('user_id', 'user_id_fk_370395')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
