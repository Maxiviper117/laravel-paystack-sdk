<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_transactions', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('billable');
            $table->foreignId('paystack_customer_id')->nullable()->constrained('paystack_customers')->nullOnDelete();
            $table->foreignId('paystack_plan_id')->nullable()->constrained('paystack_plans')->nullOnDelete();
            $table->foreignId('paystack_subscription_id')->nullable()->constrained('paystack_subscriptions')->nullOnDelete();
            $table->string('paystack_id')->nullable()->unique();
            $table->string('reference')->nullable()->unique();
            $table->string('customer_code')->nullable()->index();
            $table->string('status')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('channel')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('message')->nullable();
            $table->text('gateway_response')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('fees')->nullable();
            $table->json('metadata')->nullable();
            $table->json('authorization')->nullable();
            $table->json('log')->nullable();
            $table->json('fees_split')->nullable();
            $table->json('subaccount')->nullable();
            $table->json('split')->nullable();
            $table->string('order_id')->nullable();
            $table->unsignedInteger('requested_amount')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['status', 'currency']);
            $table->index(['paystack_customer_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_transactions');
    }
};
