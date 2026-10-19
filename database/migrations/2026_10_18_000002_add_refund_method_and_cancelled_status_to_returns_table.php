<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `returns` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'approved'");
        }

        Schema::table('returns', function (Blueprint $table) {
            if (!Schema::hasColumn('returns', 'refund_method')) {
                $table->enum('refund_method', ['cash', 'vodafone_cash', 'instapay'])->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('returns', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('returns', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('returns', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            if (Schema::hasColumn('returns', 'cancelled_by')) {
                $table->dropForeign(['cancelled_by']);
            }
            $table->dropColumn(['refund_method', 'cancellation_reason', 'cancelled_at', 'cancelled_by']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `returns` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};

