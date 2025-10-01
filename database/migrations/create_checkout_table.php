<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nezasa\Checkout\Payments\Enums\PaymentStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('checkout_id')->unique();
            $table->string('itinerary_id');
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('checkout_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('checkout_id')->constrained('checkouts', 'id')->cascadeOnDelete();
            $table->string('gateway');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->text('prepare_data')->nullable();
            $table->text('result_data')->nullable();
            $table->text('nezasa_transaction')->nullable();
            $table->string('nezasa_transaction_ref_id')->nullable();
            $table->tinyInteger('status')->default(PaymentStatusEnum::Pending->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_transactions');
        Schema::dropIfExists('checkouts');
    }
};
