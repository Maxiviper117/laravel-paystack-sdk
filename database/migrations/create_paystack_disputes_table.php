<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_disputes', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('billable');
            $table->foreignId('paystack_transaction_id')->nullable()->constrained('paystack_transactions')->nullOnDelete();
            $table->foreignId('paystack_customer_id')->nullable()->constrained('paystack_customers')->nullOnDelete();
            $table->string('paystack_id')->nullable()->unique();
            $table->string('transaction_reference')->nullable()->unique();
            $table->string('merchant_transaction_reference')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('refund_amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('resolution')->nullable();
            $table->string('domain')->nullable();
            $table->string('category')->nullable();
            $table->text('note')->nullable();
            $table->text('attachments')->nullable();
            $table->string('last4')->nullable();
            $table->string('bin')->nullable();
            $table->string('source')->nullable();
            $table->string('created_by')->nullable();
            $table->string('organization')->nullable();
            $table->string('integration')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('created_at_source')->nullable();
            $table->timestamp('updated_at_source')->nullable();
            $table->json('evidence')->nullable();
            $table->json('history')->nullable();
            $table->json('messages')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['status', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_disputes');
    }
};
