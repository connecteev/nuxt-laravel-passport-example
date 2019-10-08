<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');

            $table->string('slug');

            $table->boolean('is_featured')->default(0)->nullable();

            $table->integer('featured_order')->nullable();

            $table->boolean('is_popular')->default(0)->nullable();

            $table->integer('popular_order')->nullable();

            $table->string('tag_bg_color');

            $table->string('tag_fg_color');

            $table->string('cta_title')->nullable();

            $table->string('cta_subtitle')->nullable();

            $table->longText('intro')->nullable();

            $table->longText('submission_guidelines')->nullable();

            $table->longText('about')->nullable();

            $table->boolean('restricted')->default(0)->nullable();

            $table->boolean('active')->default(0)->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }
}
