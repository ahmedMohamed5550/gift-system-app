<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'vodafone_cash', 'instapay'])->default('cash')->after('amount');
            $table->enum('status', ['active', 'cancelled'])->default('active')->after('payment_method');
            $table->text('cancellation_reason')->nullable()->after('description');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_at');

            $table->index(['status', 'payment_method']);
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropIndex(['status', 'payment_method']);
            $table->dropIndex(['shift_id']);
            $table->dropColumn(['payment_method', 'status', 'cancellation_reason', 'cancelled_at', 'cancelled_by']);
        });
    }
};

