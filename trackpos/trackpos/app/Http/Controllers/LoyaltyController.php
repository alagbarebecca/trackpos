<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerReward;
use App\Models\RewardTransaction;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * List customers with rewards
     */
    public function index(Request $request)
    {
        $query = Customer::with('reward');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->paginate(20);
        
        // Get loyalty settings
        $pointsPerDollar = Setting::get('loyalty_points_per_dollar') ?? 1;
        $redemptionValue = Setting::get('loyalty_redemption_value') ?? 0.01;
        
        return view('loyalty.index', compact('customers', 'pointsPerDollar', 'redemptionValue'));
    }

    /**
     * Customer loyalty details
     */
    public function show(Customer $customer)
    {
        $customer->load(['reward.transactions.sale']);
        
        $transactions = RewardTransaction::whereHas('customerReward', function($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->orderByDesc('created_at')->paginate(20);
        
        return view('loyalty.show', compact('customer', 'transactions'));
    }

    /**
     * Add bonus points
     */
    public function addPoints(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        
        $reward = CustomerReward::firstOrCreate(
            ['customer_id' => $customer->id],
            ['points' => 0, 'total_points_earned' => 0, 'total_points_redeemed' => 0, 'lifetime_value' => 0]
        );

        DB::transaction(function () use ($reward, $validated) {
            $reward->increment('points', $validated['points']);
            $reward->increment('total_points_earned', $validated['points']);
            
            RewardTransaction::create([
                'customer_reward_id' => $reward->id,
                'type' => 'bonus',
                'points' => $validated['points'],
                'description' => $validated['description'] ?? 'Bonus points',
            ]);
        });

        return back()->with('success', 'Points added successfully!');
    }

    /**
     * Deduct points (redemption)
     */
    public function redeemPoints(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $reward = $customer->reward;

        if (!$reward || $reward->points < $validated['points']) {
            return back()->with('error', 'Insufficient points!');
        }

        DB::transaction(function () use ($reward, $validated) {
            $reward->decrement('points', $validated['points']);
            $reward->increment('total_points_redeemed', $validated['points']);
            
            RewardTransaction::create([
                'customer_reward_id' => $reward->id,
                'type' => 'redeem',
                'points' => -$validated['points'],
                'description' => 'Points redeemed',
            ]);
        });

        return back()->with('success', 'Points redeemed successfully!');
    }

    /**
     * Settings for loyalty program
     */
    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            Setting::updateOrCreate(['key' => 'loyalty_enabled'], ['value' => $request->loyalty_enabled ? '1' : '0']);
            Setting::updateOrCreate(['key' => 'loyalty_points_per_dollar'], ['value' => $request->points_per_dollar ?? 1]);
            Setting::updateOrCreate(['key' => 'loyalty_redemption_value'], ['value' => $request->redemption_value ?? 0.01]);
            Setting::updateOrCreate(['key' => 'loyalty_min_redeem'], ['value' => $request->min_redeem ?? 100]);
            
            return back()->with('success', 'Loyalty settings updated!');
        }

        return view('loyalty.settings');
    }

    /**
     * Auto-award points on sale
     */
    public static function awardPointsOnSale(Sale $sale)
    {
        if (!$sale->customer_id) return;
        
        $enabled = Setting::get('loyalty_enabled');
        if (!$enabled) return;

        $pointsPerDollar = floatval(Setting::get('loyalty_points_per_dollar') ?? 1);
        $points = floor($sale->total * $pointsPerDollar);
        
        if ($points < 1) return;

        $reward = CustomerReward::firstOrCreate(
            ['customer_id' => $sale->customer_id],
            ['points' => 0, 'total_points_earned' => 0, 'total_points_redeemed' => 0, 'lifetime_value' => 0]
        );

        DB::transaction(function () use ($reward, $sale, $points) {
            $reward->increment('points', $points);
            $reward->increment('total_points_earned', $points);
            $reward->increment('lifetime_value', $sale->total);
            
            RewardTransaction::create([
                'customer_reward_id' => $reward->id,
                'type' => 'earn',
                'points' => $points,
                'sale_id' => $sale->id,
                'description' => 'Purchase points',
            ]);
        });
    }
}