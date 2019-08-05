<?php

namespace App\Mail\Auth;

use App\Mail\PersonalizedEmail;
use App\Models\EmailVerification;
use App\Models\SessionLink;
use App\Utils\EmailRecipient;
use Illuminate\Mail\Mailable;

class SessionLinkMail extends PersonalizedEmail {

    /**
     * Email subject.
     */
    const SUBJECT = 'mail.auth.sessionLink.subject';

    /**
     * Email view.
     */
    const VIEW = 'mail.auth.sessionLink';

    /**
     * The worker queue to put this mailable on.
     */
    const QUEUE = 'high';

    /**
     * Token to authenticate the session with.
     * @var string
     */
    public $token;

    /**
     * Constructor.
     *
     * @param EmailRecipient|EmailRecipient[] $recipient Email recipient.
     * @param \App\Models\SessionLink $sessionLink Session link object to use
     *      the token from.
     */
    public function __construct($recipient, SessionLink $sessionLink) {
        parent::__construct($recipient, self::SUBJECT);
        $this->token = $sessionLink->token;
    }

    /**
     * Build the message.
     *
     * @return Mailable
     */
    public function build() {
        return parent::build()->markdown(self::VIEW);
    }

    /**
     * Get the worker queue to put this mailable on.
     * @return string
     */
    protected function getWorkerQueue() {
        return self::QUEUE;
    }
}
