<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTimelineEventsTable extends Migration
{
    public function up()
    {
        Schema::create('project_timeline_events', function (Blueprint $table) {
            $table->id(); // auto-incrementing primary key
            $table->unsignedBigInteger('project_id');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->timestampTz('event_date')->nullable();
            $table->text('event_type')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestampsTz();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_timeline_events');
    }
}
