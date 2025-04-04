<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderTeamMembersTable extends Migration
{
    public function up()
    {
        Schema::create('provider_team_members', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_provider_id'); // The team leader
            $table->unsignedBigInteger('member_provider_id'); // The invited member

            $table->string('role')->default('member'); // e.g. 'admin', 'member'
            $table->string('status')->default('invited'); // e.g. 'invited', 'accepted'

            $table->timestamps();

            $table->foreign('owner_provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade');

            $table->foreign('member_provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade');

            $table->unique(['owner_provider_id', 'member_provider_id'], 'ptm_owner_member_unique');

        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_team_members');
    }
}
