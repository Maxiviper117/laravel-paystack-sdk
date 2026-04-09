<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('billable');
            $table->foreignId('paystack_customer_id')->nullable()->constrained('paystack_customers')->nullOnDelete();
            $table->string('name')->default('default');
            $table->string('subscription_code')->unique();
            $table->string('status')->nullable();
            $table->string('email_token')->nullable();
            $table->string('plan_code')->nullable();
            $table->string('open_invoice')->nullable();
            $table->timestamp('next_payment_date')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['billable_type', 'billable_id', 'name']);
            $table->index(['status', 'plan_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_subscriptions');
    }
};
