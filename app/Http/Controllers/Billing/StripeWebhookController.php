<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $secret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response('Webhook signature verification failed', 400);
        }

        try {
            switch ($event->type) {
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event);
                    break;
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event);
                    break;
                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event);
                    break;
                case 'customer.updated':
                    $this->handleCustomerUpdated($event);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'event_type' => $event->type,
                'error' => $e->getMessage(),
            ]);
            return response('Webhook processing failed', 400);
        }

        return response('Success', 200);
    }

    private function handleSubscriptionUpdated($event)
    {
        $subscription = $event->data->object;
        $company = Company::where('stripe_id', $subscription->customer)->first();

        if (!$company) {
            Log::warning('Company not found for Stripe customer', [
                'stripe_customer_id' => $subscription->customer,
            ]);
            return;
        }

        // Update or create local subscription
        $localSubscription = Subscription::updateOrCreate(
            ['stripe_id' => $subscription->id],
            [
                'company_id' => $company->id,
                'name' => $subscription->items->data[0]->price->nickname ?? 'Subscription',
                'stripe_status' => $subscription->status,
                'stripe_price' => $subscription->items->data[0]->price->id,
                'quantity' => $subscription->items->data[0]->quantity,
                'trial_ends_at' => $subscription->trial_end ? now()->setTimestamp($subscription->trial_end) : null,
                'ends_at' => $subscription->cancel_at ? now()->setTimestamp($subscription->cancel_at) : null,
            ]
        );

        // Update company
        $company->update([
            'stripe_status' => $subscription->status,
            'subscribed_until' => $subscription->current_period_end ? now()->setTimestamp($subscription->current_period_end) : null,
        ]);

        Log::info('Subscription updated', [
            'company_id' => $company->id,
            'stripe_subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);
    }

    private function handleSubscriptionDeleted($event)
    {
        $subscription = $event->data->object;
        $company = Company::where('stripe_id', $subscription->customer)->first();

        if (!$company) {
            Log::warning('Company not found for Stripe customer', [
                'stripe_customer_id' => $subscription->customer,
            ]);
            return;
        }

        // Update local subscription
        $localSubscription = Subscription::where('stripe_id', $subscription->id)->first();
        if ($localSubscription) {
            $localSubscription->update([
                'stripe_status' => $subscription->status,
                'ends_at' => now(),
            ]);
        }

        // Update company
        $company->update([
            'stripe_status' => $subscription->status,
            'subscribed_until' => now(),
        ]);

        Log::info('Subscription deleted', [
            'company_id' => $company->id,
            'stripe_subscription_id' => $subscription->id,
        ]);
    }

    private function handleInvoicePaymentSucceeded($event)
    {
        $invoice = $event->data->object;
        $company = Company::where('stripe_id', $invoice->customer)->first();

        if ($company) {
            Log::info('Invoice payment succeeded', [
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_paid,
            ]);
        }
    }

    private function handleInvoicePaymentFailed($event)
    {
        $invoice = $event->data->object;
        $company = Company::where('stripe_id', $invoice->customer)->first();

        if ($company) {
            Log::warning('Invoice payment failed', [
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_due,
            ]);

            // You might want to send a notification to the company about the failed payment
            // This could trigger email notifications, downgrade processes, etc.
        }
    }

    private function handleCustomerUpdated($event)
    {
        $customer = $event->data->object;
        $company = Company::where('stripe_id', $customer->id)->first();

        if ($company) {
            // Update company information from Stripe
            $company->update([
                'email' => $customer->email,
                'name' => $customer->name,
            ]);

            Log::info('Customer updated', [
                'company_id' => $company->id,
                'customer_id' => $customer->id,
            ]);
        }
    }
}