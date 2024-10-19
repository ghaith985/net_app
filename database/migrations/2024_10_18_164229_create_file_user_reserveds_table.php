<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_user_reserveds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreignId('user_id')->constrained('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_user_reserveds');
    }
};
