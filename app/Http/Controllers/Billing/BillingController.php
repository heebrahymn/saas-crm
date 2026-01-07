<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function plans(Request $request)
    {
        $plans = Plan::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();

        return response()->json([
            'plans' => $plans,
        ]);
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $company = $request->attributes->get('tenant');
        $plan = Plan::findOrFail($validated['plan_id']);

        $result = $this->subscriptionService->subscribe($company, $plan);

        if (!$result['success']) {
            return response()->json([
                'message' => 'Subscription failed',
                'error' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Subscribed successfully',
            'subscription' => $result['subscription'],
            'plan' => $plan,
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $company = $request->attributes->get('tenant');

        $result = $this->subscriptionService->cancel($company);

        if (!$result['success']) {
            return response()->json([
                'message' => 'Unsubscription failed',
                'error' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Subscription cancelled successfully',
        ]);
    }

    public function changePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $company = $request->attributes->get('tenant');
        $newPlan = Plan::findOrFail($validated['plan_id']);

        $result = $this->subscriptionService->changePlan($company, $newPlan);

        if (!$result['success']) {
            return response()->json([
                'message' => 'Plan change failed',
                'error' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Plan changed successfully',
            'subscription' => $result['subscription'],
            'plan' => $newPlan,
        ]);
    }

    public function currentSubscription(Request $request)
    {
        $company = $request->attributes->get('tenant');
        $subscription = $this->subscriptionService->getCompanySubscription($company);
        $plan = $this->subscriptionService->getCompanyPlan($company);

        return response()->json([
            'subscription' => $subscription,
            'plan' => $plan,
            'is_subscribed' => $company->isSubscribed(),
            'is_on_trial' => $company->isOnTrial(),
        ]);
    }

    public function invoices(Request $request)
    {
        $company = $request->attributes->get('tenant');

        $result = $this->subscriptionService->getInvoices($company);

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to retrieve invoices',
                'error' => $result['error']
            ], 400);
        }

        return response()->json([
            'invoices' => $result['invoices'],
        ]);
    }

    public function syncStatus(Request $request)
    {
        $company = $request->attributes->get('tenant');

        $result = $this->subscriptionService->syncSubscriptionStatus($company);

        if (!$result['success']) {
            return response()->json([
                'message' => 'Failed to sync subscription status',
                'error' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Subscription status synced successfully',
            'subscriptions' => $result['subscriptions'],
        ]);
    }
}