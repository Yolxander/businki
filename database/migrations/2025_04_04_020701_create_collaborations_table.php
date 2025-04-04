<?php

// database/migrations/xxxx_xx_xx_create_collaborations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollaborationsTable extends Migration
{
    public function up()
    {
        Schema::create('collaborations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('inviter_id');   // Provider who invites
            $table->unsignedBigInteger('invitee_id');   // Provider invited

            $table->string('status')->default('invited'); // invited, accepted, declined
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('inviter_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('invitee_id')->references('id')->on('providers')->onDelete('cascade');

            $table->unique(['project_id', 'invitee_id']); // prevent duplicate invites to same project
        });
    }

    public function down()
    {
        Schema::dropIfExists('collaborations');
    }
}
