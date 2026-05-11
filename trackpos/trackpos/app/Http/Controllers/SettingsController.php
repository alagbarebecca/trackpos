<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [];
        
        // Check if settings table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $settings = Setting::pluck('value', 'key')->toArray();
        }
        
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:10',
            'language' => 'required|string|max:50',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email',
            'company_tax_id' => 'nullable|string|max:100',
            'invoice_logo' => 'nullable|string|max:500',
            'invoice_footer' => 'nullable|string',
            'default_discount_type' => 'nullable|in:percentage,fixed',
            'default_discount_value' => 'nullable|numeric|min:0',
            'bulk_discount_threshold' => 'nullable|integer|min:1',
            'bulk_discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $fields = [
            'app_name', 'currency_symbol', 'language', 'default_tax_rate',
            'company_name', 'company_address', 'company_phone', 'company_email', 'company_tax_id',
            'invoice_logo', 'invoice_footer',
            'default_discount_type', 'default_discount_value',
            'bulk_discount_threshold', 'bulk_discount_percent'
        ];

        foreach ($fields as $field) {
            Setting::updateOrCreate(
                ['key' => $field],
                ['value' => $request->input($field)]
            );
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }

    public function get($key)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }

    public function all()
    {
        return Setting::pluck('value', 'key')->toArray();
    }
}