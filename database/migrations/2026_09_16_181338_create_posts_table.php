<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The posts table already exists in Supabase, so it is only created
     * where it is missing (e.g. the in-memory test database).
     */
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            return;
        }

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('content');
            $table->text('author')->nullable();
            $table->boolean('published')->nullable()->default(true);
            $table->timestampTz('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally does not drop the table, since it is owned by Supabase.
     */
    public function down(): void
    {
        //
    }
};
