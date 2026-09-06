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
        Schema::table('saturday_attendances', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->change();
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();
            $table->timestamp('captured_at')->nullable()->change();
        });

        Schema::table('saturday_attendances', function (Blueprint $table) {
            $table->foreignId('marked_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('note', 280)->nullable()->after('accuracy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saturday_attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marked_by');
            $table->dropColumn('note');
        });
    }
};
