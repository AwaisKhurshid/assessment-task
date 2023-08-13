<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Services\AffiliateService; // Import the AffiliateService class


class OrderService
{
    protected $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */

public function processOrder(array $data)
{
    // Check if an order with the same order_id already exists
    $existingOrder = Order::where('id', $data['order_id'])->first();
    if ($existingOrder) {
        return; // Ignore duplicates
    }

    // Create or retrieve a merchant based on the provided merchant_domain
    $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
    if (!$merchant) {
        // Handle the case when the merchant doesn't exist
        return;
    }

    // Create a new order and associate it with the merchant
    $order = new Order();
    $order->id = 34343;
    $order->subtotal = $data['subtotal_price'];
    $order->merchant_id = $merchant->id; // Associate the order with the merchant
    // ... Fill in other order properties ...
    $order->save();

    // Create or retrieve an affiliate based on customer_email
    $affiliate = Affiliate::where('email', $data['customer_email'])->first();
    if (!$affiliate) {
        $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 10.0);
    }

    // Associate the order with the affiliate, if available
    if ($affiliate) {
        $order->affiliate_id = $affiliate->id;
        $order->commission_owed = $order->subtotal * ($affiliate->commission_percentage / 100);
        $order->save();
    }

}


}
