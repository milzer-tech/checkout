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
            $table->string('checkout_id');
            $table->string('itinerary_id');
            $table->char('origin', 3);
            $table->char('lang', 2)->nullable();
            $table->boolean('rest_payment')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['checkout_id', 'itinerary_id']);
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
};
