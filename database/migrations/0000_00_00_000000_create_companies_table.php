<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('email');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_until')->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('stripe_status')->nullable();
            $table->string('stripe_price')->nullable();
            $table->timestamp('trial_will_end_at')->nullable();
            $table->timestamp('card_brand')->nullable();
            $table->timestamp('card_last_four')->nullable();
            $table->timestamp('card_country')->nullable();
            $table->timestamp('billing_address')->nullable();
            $table->timestamp('billing_city')->nullable();
            $table->timestamp('billing_state')->nullable();
            $table->timestamp('billing_zip')->nullable();
            $table->timestamp('billing_country')->nullable();
            $table->timestamp('tax_exempt')->nullable();
            $table->timestamp('extra_billing_information')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};