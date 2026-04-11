<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_refunds', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('billable');
            $table->foreignId('paystack_transaction_id')->nullable()->constrained('paystack_transactions')->nullOnDelete();
            $table->foreignId('paystack_customer_id')->nullable()->constrained('paystack_customers')->nullOnDelete();
            $table->string('paystack_id')->nullable()->unique();
            $table->string('refund_reference')->nullable()->unique();
            $table->string('transaction_reference')->nullable()->index();
            $table->string('status')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->unsignedInteger('deducted_amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('channel')->nullable();
            $table->boolean('fully_deducted')->nullable();
            $table->string('refunded_by')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('expected_at')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('merchant_note')->nullable();
            $table->string('bank_reference')->nullable();
            $table->text('reason')->nullable();
            $table->string('initiated_by')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->string('session_id')->nullable();
            $table->string('processor')->nullable();
            $table->string('integration')->nullable();
            $table->string('domain')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['status', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_refunds');
    }
};
