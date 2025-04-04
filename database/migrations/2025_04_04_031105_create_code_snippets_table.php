<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeSnippetsTable extends Migration
{
    public function up()
    {
        Schema::create('code_snippets', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('code');
            $table->string('language')->nullable();

            // Polymorphic relation
            $table->morphs('snippable'); // creates snippable_id (int) and snippable_type (string)

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('code_snippets');
    }
}
