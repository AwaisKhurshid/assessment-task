<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     * @throws AffiliateCreateException
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Check if the email is already used as a merchant's email
        if ($merchant->user->email === $email) {
            throw new AffiliateCreateException('Email is already in use by a merchant.');
        }

        // Check if the email is already used as an affiliate's email
        if (Affiliate::where('email', $email)->exists()) {
            throw new AffiliateCreateException('Email is already in use by an affiliate.');
        }

        // Create a new affiliate
        $affiliate = new Affiliate();
        $affiliate->merchant_id = $merchant->id;
        $affiliate->email = $email;
        $affiliate->user_id = $merchant->user->id;
        // $affiliate->name = $name;
        $affiliate->commission_rate = $commissionRate;
        // Generate a discount code using the ApiService
        $discountCodeData = $this->apiService->createDiscountCode($merchant);
        $affiliate->discount_code = $discountCodeData['code'];
        // $affiliate->save();

        $affiliate->save();

        // Send an affiliate created email (you may need to implement the AffiliateCreated email)
        Mail::to($email)->send(new AffiliateCreated($affiliate));
        // dd($affiliate);

        return $affiliate;
    }
}
