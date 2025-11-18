<?php

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
        // Modify the payment_method ENUM to include 'bank_paybill'
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'mpesa', 'bank_paybill', 'card', 'insurance', 'other') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'mpesa', 'card', 'insurance', 'other') DEFAULT 'cash'");
    }
};
