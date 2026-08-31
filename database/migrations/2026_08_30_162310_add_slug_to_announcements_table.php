<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        $used = [];

        DB::table('announcements')->orderBy('id')->get()->each(function (object $announcement) use (&$used): void {
            $base = Str::slug((string) $announcement->title);
            $base = $base !== '' ? $base : 'pengumuman';
            $slug = $base;
            $suffix = 2;

            while (in_array($slug, $used, true)) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $used[] = $slug;

            DB::table('announcements')->where('id', $announcement->id)->update([
                'slug' => $slug,
            ]);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
