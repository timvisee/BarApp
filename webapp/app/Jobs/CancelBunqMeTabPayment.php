<?php

namespace App\Jobs;

use App\Models\BunqAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use bunq\Model\Generated\Endpoint\BunqMeTab;

// TODO: always use high priority for this job
class CancelBunqMeTabPayment implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the bunq account, which the money is sent from.
     *
     * @var int
     */
    private $account_id;

    /**
     * The BunqMe Tab ID.
     *
     * @var int
     */
    private $tab_id;

    /**
     * Create a new job instance.
     *
     * @param BunqAccount $account The bunq account to send the money from.
     * @param int $bunqMeTabId BunqMe Tab ID.
     *
     * @return void
     */
    public function __construct(BunqAccount $account, int $bunqMeTabId) {
        $this->account_id = $account->id;
        $this->tab_id = $bunqMeTabId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        // Find the bunq account, load the bunq API context
        $account = BunqAccount::findOrFail($this->account_id);
        $account->loadBunqContext();

        // Cancel the request
        BunqMeTab::update(
            $this->tab_id,
            $account->monetary_account_id,
            'CANCELLED'
        );
    }
}
