<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;

class SubscriptionService
{
    public function __construct(private StripeService $stripeService) {}

    public function subscribe(Company $company, Plan $plan, array $options = []): array
    {
        $result = $this->stripeService->createSubscription($company, $plan, $options);

        if (!$result['success']) {
            return $result;
        }

        $stripeSubscription = $result['subscription'];

        // Create local subscription record
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'name' => $plan->name,
            'stripe_id' => $stripeSubscription->id,
            'stripe_status' => $stripeSubscription->status,
            'stripe_price' => $stripeSubscription->items->data[0]->price->id,
            'quantity' => $stripeSubscription->items->data[0]->quantity,
            'trial_ends_at' => $stripeSubscription->trial_end ? now()->setTimestamp($stripeSubscription->trial_end) : null,
            'ends_at' => $stripeSubscription->cancel_at ? now()->setTimestamp($stripeSubscription->cancel_at) : null,
        ]);

        // Update company with subscription info
        $company->update([
            'stripe_id' => $stripeSubscription->customer,
            'stripe_status' => $stripeSubscription->status,
            'stripe_price' => $stripeSubscription->items->data[0]->price->id,
            'subscribed_until' => $stripeSubscription->current_period_end ? now()->setTimestamp($stripeSubscription->current_period_end) : null,
        ]);

        // Clear cache
        Cache::forget("tenant:{$company->id}:subscribed");

        return [
            'success' => true,
            'subscription' => $subscription,
            'stripe_subscription' => $stripeSubscription,
        ];
    }

    public function cancel(Company $company, bool $atPeriodEnd = true): array
    {
        $result = $this->stripeService->cancelSubscription($company, $atPeriodEnd);

        if (!$result['success']) {
            return $result;
        }

        $stripeSubscription = $result['subscription'];

        // Update local subscription record
        $subscription = Subscription::where('company_id', $company->id)
            ->where('stripe_id', $stripeSubscription->id)
            ->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'ends_at' => $stripeSubscription->cancel_at ? now()->setTimestamp($stripeSubscription->cancel_at) : null,
            ]);
        }

        // Update company
        $company->update([
            'stripe_status' => $stripeSubscription->status,
            'subscribed_until' => $stripeSubscription->cancel_at ? now()->setTimestamp($stripeSubscription->cancel_at) : null,
        ]);

        // Clear cache
        Cache::forget("tenant:{$company->id}:subscribed");

        return [
            'success' => true,
            'subscription' => $subscription,
        ];
    }

    public function changePlan(Company $company, Plan $newPlan): array
    {
        $result = $this->stripeService->updateSubscription($company, $newPlan);

        if (!$result['success']) {
            return $result;
        }

        $stripeSubscription = $result['subscription'];

        // Update local subscription record
        $subscription = Subscription::where('company_id', $company->id)
            ->where('stripe_id', $stripeSubscription->id)
            ->first();

        if ($subscription) {
            $subscription->update([
                'name' => $newPlan->name,
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $stripeSubscription->items->data[0]->price->id,
                'subscribed_until' => now()->setTimestamp($stripeSubscription->current_period_end),
            ]);
        }

        // Update company
        $company->update([
            'stripe_status' => $stripeSubscription->status,
            'stripe_price' => $stripeSubscription->items->data[0]->price->id,
            'subscribed_until' => now()->setTimestamp($stripeSubscription->current_period_end),
        ]);

        // Clear cache
        Cache::forget("tenant:{$company->id}:subscribed");

        return [
            'success' => true,
            'subscription' => $subscription,
        ];
    }

    public function getCompanySubscription(Company $company): ?Subscription
    {
        return Subscription::where('company_id', $company->id)->first();
    }

    public function getCompanyPlan(Company $company): ?Plan
    {
        $subscription = $this->getCompanySubscription($company);
        
        if (!$subscription) {
            return null;
        }

        return Plan::where('stripe_price_id', $subscription->stripe_price)->first();
    }

    public function isSubscribed(Company $company): bool
    {
        return $company->isSubscribed();
    }

    public function getInvoices(Company $company): array
    {
        return $this->stripeService->getCustomerInvoices($company);
    }

    public function syncSubscriptionStatus(Company $company): array
    {
        $result = $this->stripeService->getSubscriptionStatus($company);

        if (!$result['success']) {
            return $result;
        }

        $stripeSubscriptions = $result['subscriptions'];

        foreach ($stripeSubscriptions as $stripeSubscription) {
            $localSubscription = Subscription::where('company_id', $company->id)
                ->where('stripe_id', $stripeSubscription->id)
                ->first();

            if ($localSubscription) {
                $localSubscription->update([
                    'stripe_status' => $stripeSubscription->status,
                    'ends_at' => $stripeSubscription->cancel_at ? now()->setTimestamp($stripeSubscription->cancel_at) : null,
                ]);
            }
        }

        // Update company status
        $activeSubscription = collect($stripeSubscriptions)->firstWhere('status', 'active');
        
        if ($activeSubscription) {
            $company->update([
                'stripe_status' => $activeSubscription->status,
                'subscribed_until' => now()->setTimestamp($activeSubscription->current_period_end),
            ]);
        }

        // Clear cache
        Cache::forget("tenant:{$company->id}:subscribed");

        return [
            'success' => true,
            'subscriptions' => $stripeSubscriptions,
        ];
    }
}