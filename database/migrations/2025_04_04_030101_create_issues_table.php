<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuesTable extends Migration
{
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->unsignedBigInteger('task_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // 'open', 'resolved', etc.
            $table->text('fix')->nullable();
            $table->timestampsTz();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('issues');
    }
}
