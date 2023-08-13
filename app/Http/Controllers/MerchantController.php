<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
{
    // Retrieve 'from' and 'to' date values from the request
    $from = $request->input('from');
    $to = $request->input('to');

    // Convert the date strings to Carbon instances for comparison
    $fromDate = Carbon::parse($from);
    $toDate = Carbon::parse($to);

    // Retrieve orders within the specified date range for the authenticated merchant
    $merchantId = auth()->user()->merchant->id;
    $orders = Order::where('merchant_id', $merchantId)
        ->whereBetween('created_at', [$fromDate, $toDate])
        ->get();

    // Calculate the sum of unpaid commissions for orders with an affiliate
    $commissionOwed = $orders->sum(function ($order) {
        return $order->affiliate ? $order->commission_owed : 0;
    });

    // Build the response array with the order count, total revenue, and commissions owed
    $response = [
        'count' => $orders->count(),
        'revenue' => $orders->sum('subtotal'),
        'commissions_owed' => $commissionOwed
    ];

    // Return a JSON response with the calculated statistics
    return response()->json($response);
}

}
