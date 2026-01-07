<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\Plan;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createCustomer(Company $company): array
    {
        try {
            $customer = $this->stripe->customers->create([
                'name' => $company->name,
                'email' => $company->email,
                'description' => "Customer for {$company->name}",
                'metadata' => [
                    'company_id' => $company->id,
                ],
            ]);

            return [
                'success' => true,
                'customer' => $customer,
            ];
        } catch (Exception $e) {
            Log::error('Stripe customer creation failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createSubscription(Company $company, Plan $plan, array $options = []): array
    {
        try {
            // Create or get customer
            if (!$company->stripe_id) {
                $customerResult = $this->createCustomer($company);
                if (!$customerResult['success']) {
                    return $customerResult;
                }
                $customer = $customerResult['customer'];
            } else {
                $customer = $this->stripe->customers->retrieve($company->stripe_id);
            }

            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => $plan->stripe_price_id],
                ],
                'trial_period_days' => $options['trial_days'] ?? null,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return [
                'success' => true,
                'subscription' => $subscription,
            ];
        } catch (Exception $e) {
            Log::error('Stripe subscription creation failed', [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function cancelSubscription(Company $company, bool $atPeriodEnd = true): array
    {
        try {
            if (!$company->stripe_id) {
                return [
                    'success' => false,
                    'error' => 'Company does not have a Stripe customer',
                ];
            }

            $subscriptions = $this->stripe->subscriptions->all([
                'customer' => $company->stripe_id,
                'status' => 'active',
            ]);

            if (empty($subscriptions->data)) {
                return [
                    'success' => false,
                    'error' => 'No active subscriptions found',
                ];
            }

            $subscription = $subscriptions->data[0];
            
            $canceledSubscription = $this->stripe->subscriptions->update(
                $subscription->id,
                ['cancel_at_period_end' => $atPeriodEnd]
            );

            return [
                'success' => true,
                'subscription' => $canceledSubscription,
            ];
        } catch (Exception $e) {
            Log::error('Stripe subscription cancellation failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateSubscription(Company $company, Plan $newPlan): array
    {
        try {
            if (!$company->stripe_id) {
                return [
                    'success' => false,
                    'error' => 'Company does not have a Stripe customer',
                ];
            }

            $subscriptions = $this->stripe->subscriptions->all([
                'customer' => $company->stripe_id,
                'status' => 'active',
            ]);

            if (empty($subscriptions->data)) {
                return [
                    'success' => false,
                    'error' => 'No active subscriptions found',
                ];
            }

            $subscription = $subscriptions->data[0];

            $updatedSubscription = $this->stripe->subscriptions->update($subscription->id, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $newPlan->stripe_price_id,
                    ],
                ],
                'proration_behavior' => 'create_prorations',
            ]);

            return [
                'success' => true,
                'subscription' => $updatedSubscription,
            ];
        } catch (Exception $e) {
            Log::error('Stripe subscription update failed', [
                'company_id' => $company->id,
                'plan_id' => $newPlan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getCustomerInvoices(Company $company): array
    {
        try {
            if (!$company->stripe_id) {
                return [
                    'success' => false,
                    'error' => 'Company does not have a Stripe customer',
                ];
            }

            $invoices = $this->stripe->invoices->all([
                'customer' => $company->stripe_id,
                'limit' => 20,
            ]);

            return [
                'success' => true,
                'invoices' => $invoices->data,
            ];
        } catch (Exception $e) {
            Log::error('Stripe invoices retrieval failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getSubscriptionStatus(Company $company): array
    {
        try {
            if (!$company->stripe_id) {
                return [
                    'success' => false,
                    'error' => 'Company does not have a Stripe customer',
                ];
            }

            $subscriptions = $this->stripe->subscriptions->all([
                'customer' => $company->stripe_id,
            ]);

            return [
                'success' => true,
                'subscriptions' => $subscriptions->data,
            ];
        } catch (Exception $e) {
            Log::error('Stripe subscription status retrieval failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}