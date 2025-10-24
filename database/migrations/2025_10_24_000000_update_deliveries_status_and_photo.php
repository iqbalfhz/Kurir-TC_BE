<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set all existing statuses to 'selesai'
        DB::table('deliveries')->update(['status' => 'selesai']);

        // For any existing rows with null photo, set a placeholder path so the NOT NULL
        // constraint can be applied safely. You may replace the placeholder with a real
        // default image later.
        DB::table('deliveries')->whereNull('photo')->update(['photo' => 'deliveries/placeholder.svg']);

        // Ensure a placeholder image exists in the public disk so URLs resolve.
        // The placeholder file is stored in resources/images/placeholder.svg and copied
        // into storage/app/public/deliveries/placeholder.svg during migration.
        $src = base_path('resources/images/placeholder.svg');
        $dstDir = storage_path('app/public/deliveries');
        $dst = $dstDir . DIRECTORY_SEPARATOR . 'placeholder.svg';
        if (file_exists($src)) {
            if (!is_dir($dstDir)) {
                mkdir($dstDir, 0755, true);
            }
            // copy will overwrite an existing file, which is fine for idempotency
            copy($src, $dst);
        }

        // Make photo column NOT NULL and set default status to 'selesai'
        // Note: Changing columns requires doctrine/dbal package to be installed.
        Schema::table('deliveries', function (Blueprint $table) {
            // change status default
            $table->string('status')->default('selesai')->change();
            // make photo NOT NULL
            $table->string('photo')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // revert to previous defaults/nullable (previous default: 'delivered')
            $table->string('status')->default('delivered')->change();
            $table->string('photo')->nullable()->change();
        });
    }
};
