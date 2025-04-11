<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixClientIdForeignKeyOnProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop the incorrect FK if it exists (safely ignore errors if it doesn't)
            try {
                $table->dropForeign(['client_id']);
            } catch (\Exception $e) {
                \Log::warning('Failed to drop old client_id foreign key (maybe already dropped): ' . $e->getMessage());
            }

            // Add correct foreign key to clients.id
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            // Restore the old foreign key (to users.id) if needed
            $table->foreign('client_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
}
