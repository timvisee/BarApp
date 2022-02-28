<?php

namespace App\Mail\Email;

use App\Mail\PersonalizedEmail;
use App\Managers\EmailVerificationManager;
use App\Models\EmailVerification;
use App\Utils\EmailRecipient;
use Illuminate\Mail\Mailable;

class Verify extends PersonalizedEmail {

    /**
     * Email subject normal verification messages.
     */
    const SUBJECT = 'mail.email.verify.subject';

    /**
     * The view to use when users have just registered.
     */
    const VIEW_REGISTERED = 'mail.email.registerAndVerify';

    /**
     * The view to use normally.
     */
    const VIEW = 'mail.email.verify';

    /**
     * The worker queue to put this mailable on.
     */
    const QUEUE = 'high';

    /**
     * Defines whether the user has just registered.
     *
     * @var bool True if just registered, false if not.
     */
    private $justRegistered;

    /**
     * Email verification token.
     * @var string
     */
    public $token;

    /**
     * Verify constructor.
     *
     * @param EmailRecipient $recipient Email recipient.
     * @param \App\Models\EmailVerification $emailVerification Email verification model to use the token from.
     * @param bool $justRegistered=false True if just registered, false if not.
     */
    public function __construct(EmailRecipient $recipient, EmailVerification $emailVerification, $justRegistered = false) {
        // Construct the parent, dynamically figure out the subject
        parent::__construct($recipient, self::SUBJECT);

        $this->token = $emailVerification->token;
        $this->justRegistered = $justRegistered;
    }

    /**
     * Build the message.
     *
     * @return Mailable
     */
    public function build() {
        // Localize expire time, force set correct locale for this
        $this->configureLocale();
        $expire = now()
            ->addSeconds(EmailVerificationManager::EXPIRE_AFTER + 1)
            ->longAbsoluteDiffForHumans();

        return parent::build()
            ->markdown(
                $this->justRegistered ? self::VIEW_REGISTERED : self::VIEW
            )
            ->with('expire', $expire);
    }

    /**
     * Get the worker queue to put this mailable on.
     * @return string
     */
    protected function getWorkerQueue() {
        return self::QUEUE;
    }

    /**
     * Backoff times in seconds.
     *
     * @return array
     */
    public function backoff() {
        // Quickly retry, this email is important, we want it fast
        return [1, 1, 2, 3, 5, 8, 10];
    }

    public function retryUntil() {
        // It does not make sense to send when it has already expired,
        // require at least a minute left
        return now()->addSeconds(EmailVerificationManager::EXPIRE_AFTER)->subMinute();
    }
}
