<?php

namespace App\Providers;

use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;
use App\Events\WalletBalanceChange;
use App\Listeners\SendBalanceBelowZeroNotification;
use App\Listeners\SendPaymentCompleteNotification;
use App\Listeners\SendPaymentFailNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PaymentCompleted::class => [
            SendPaymentCompleteNotification::class,
        ],
        PaymentFailed::class => [
            SendPaymentFailNotification::class,
        ],
        WalletBalanceChange::class => [
            SendBalanceBelowZeroNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        //
    }
}
