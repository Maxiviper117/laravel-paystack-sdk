<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('paystack_id')->nullable()->unique();
            $table->string('plan_code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->string('interval')->nullable();
            $table->text('description')->nullable();
            $table->string('currency')->nullable();
            $table->unsignedInteger('invoice_limit')->nullable();
            $table->boolean('send_invoices')->nullable();
            $table->boolean('send_sms')->nullable();
            $table->json('payload_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_plans');
    }
};
