<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order,
        public bool $payoutSuccessful = false
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    // public function handle(ApiService $apiService)
    // {
    //     try {
    //         // Attempt to send the payout using the API service
    //         $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

    //         // If the payout is successful, update the order's payout_status to paid
    //         DB::transaction(function () {
    //             $this->order->update(['payout_status' => Order::STATUS_PAID]);
    //         });

    //         $this->payoutSuccessful = true;
    //     } catch (\Exception $e) {
    //         // If an exception is thrown, the payout is not successful
    //         // Keep the order's payout_status as unpaid
    //         $this->payoutSuccessful = false;
    //     }
    // }
    public function handle(ApiService $apiService)
{
    // Attempt to send the payout using the API service
    $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

    // If the payout is successful, update the order's payout_status to paid
    DB::transaction(function () {
        $this->order->update(['payout_status' => Order::STATUS_PAID]);
    });

    $this->payoutSuccessful = true;
}

}
