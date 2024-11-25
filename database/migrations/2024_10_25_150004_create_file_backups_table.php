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
        Schema::create('file_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->text('file_data'); // يمكن أن تخزن هنا البيانات بصيغة JSON أو محتوى الملف نفسه
            $table->string('version')->nullable(); // رقم الإصدار
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_backups');
    }
};
