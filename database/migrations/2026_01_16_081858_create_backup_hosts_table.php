<?php

use App\Models\BackupHost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backup_hosts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('schema');
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_host_node', function (Blueprint $table) {
            $table->unsignedInteger('node_id');
            $table->foreign('node_id')->references('id')->on('nodes')->cascadeOnDelete();
            $table->unsignedInteger('backup_host_id');
            $table->foreign('backup_host_id')->references('id')->on('backup_hosts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['node_id']);
        });

        // Add the column as nullable first so the ALTER succeeds on installs
        // that already have rows in `backups`. It is tightened to NOT NULL
        // further down, after every existing row has been backfilled.
        Schema::table('backups', function (Blueprint $table) {
            $table->unsignedInteger('backup_host_id')->nullable()->after('disk');
            $table->foreign('backup_host_id')->references('id')->on('backup_hosts');
            $table->dropColumn('disk');
        });

        $oldDriver = env('APP_BACKUP_DRIVER', 'wings');

        $oldConfiguration = null;
        if ($oldDriver === 's3') {
            $oldConfiguration = [
                'region' => env('AWS_DEFAULT_REGION'),
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'bucket' => env('AWS_BACKUPS_BUCKET'),
                'prefix' => env('AWS_BACKUPS_BUCKET', ''),
                'endpoint' => env('AWS_ENDPOINT'),
                'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'use_accelerate_endpoint' => env('AWS_BACKUPS_USE_ACCELERATE', false),
                'storage_class' => env('AWS_BACKUPS_STORAGE_CLASS'),
            ];
        }

        $backupHost = BackupHost::create([
            'name' => $oldDriver === 's3' ? 'Remote' : 'Local',
            'schema' => $oldDriver,
            'configuration' => $oldConfiguration,
        ]);

        DB::table('backups')->update(['backup_host_id' => $backupHost->id]);

        // Every row now has a value; enforce the constraint the original
        // migration intended.
        Schema::table('backups', function (Blueprint $table) {
            $table->unsignedInteger('backup_host_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mirror of the fix in up(): re-add `disk` as nullable, backfill it
        // from each backup host's schema, then enforce NOT NULL.
        Schema::table('backups', function (Blueprint $table) {
            $table->string('disk')->nullable()->after('backup_host_id');
        });

        DB::statement('UPDATE backups SET disk = backup_hosts.schema FROM backup_hosts WHERE backups.backup_host_id = backup_hosts.id');

        Schema::table('backups', function (Blueprint $table) {
            $table->string('disk')->nullable(false)->change();
            $table->dropForeign(['backup_host_id']);
            $table->dropColumn('backup_host_id');
        });

        // backup_host_node references backup_hosts, so it must be dropped first.
        Schema::dropIfExists('backup_host_node');
        Schema::dropIfExists('backup_hosts');
    }
};
