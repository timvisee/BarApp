<?php

namespace App\Mail;

use App\Utils\EmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class PersonalizedEmail extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    /**
     * The default queue to put this mailable on.
     */
    const QUEUE_DEFAULT = 'normal';

    /**
     * The recipients that will receive the email.
     * This may be multiple recipients with different email addresses for the same user.
     * @var EmailRecipient[]
     */
    public $recipients;

    /**
     * Reply-To address.
     * @var string|null
     */
    public $reply_to_address = null;

    /**
     * The language key for the subject of the message.
     * @var string
     */
    public $subjectKey = null;

    /**
     * The language values for the subject of the message.
     * @var string
     */
    public $subjectValues = null;

    /**
     * Constructor.
     *
     * @param EmailRecipient[] $recipients Email recipients, may be a single one.
     * @param string $subjectKey Message subject language key.
     * @param array $subjectValues Fields to replace in the subject language value.
     */
    public function __construct($recipients, $subjectKey, array $subjectValues = []) {
        $this->recipients = collect([$recipients])->flatten();
        $this->subjectKey = $subjectKey;
        $this->subjectValues = $subjectValues;

        // There must be at least one recipient
        if($this->recipients->isEmpty())
            throw new \Exception('No recipients specified for mailable');

        // All email recipients must have the same target user
        if($this->recipients->pluck('user')->unique()->count() > 1)
            throw new \Exception('Failed to send mailable, sending to recipients being different users, should send separately');
    }

    /**
     * Configure the locale to use in this mailable.
     *
     * This will set the application locale.
     *
     * @param User $user The user instance if known.
     */
    public function configureLocale($user = null) {
        // Select mail locale, from user, or default, then set for env
        $user ??= $this->recipients->first()->getUser();
        if($user != null)
            $locale = $user->preferredLocale();
        if(empty($locale))
            $locale = $this->recipients->first()->default_locale;
        if(!empty($locale))
            set_env_locale($locale);
        if($user != null)
            $this->locale($user);
    }

    /**
     * Build the message.
     *
     * @return Mailable
     */
    public function build() {
        // Set recipient
        $this->to($this->recipients);
        if(!empty($this->reply_to_address))
            $this->replyTo($this->reply_to_address);

        // Gather recipient user, force set application locale for email
        $user = $this->recipients->first()->getUser();
        $this->configureLocale($user);

        // Format and set subject
        $subject = trans($this->subjectKey, $this->subjectValues);
        $this->subject($subject);

        // Add unsubscribe link
        $this->withSwiftMessage(function($message) {
            $message->getHeaders()
                ->addTextHeader('List-Unsubscribe', '<' . route('email.preferences') . '>');
        });

        // Finalize building mailable
        return $this
            ->onQueue($this->getWorkerQueue())
            ->with('user', $user)
            ->with('subject', $subject);
    }

    /**
     * Get the worker queue to put this mailable on.
     *
     * @return string|null The name of the queue, or null for default.
     */
    protected function getWorkerQueue() {
        return self::QUEUE_DEFAULT;
    }
}
