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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained()->onDelete('restrict');
            $table->string('dosage')->nullable(); // e.g., "500mg twice daily"
            $table->integer('quantity')->default(1);
            $table->text('instructions')->nullable();
            $table->enum('status', ['pending', 'dispensed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->index('prescription_id');
            $table->index('medicine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
