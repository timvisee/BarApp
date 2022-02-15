<?php

namespace App\Managers;

use App\Jobs\CommitBalanceUpdatesForUser;
use App\Mail\Email\Verify;
use App\Models\BalanceImportAlias;
use App\Models\Email;
use App\Models\EmailVerification;
use App\Utils\EmailRecipient;
use App\Utils\TokenGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;

class EmailVerificationManager {

    /**
     * The number of seconds a email verification expires after.
     * @type int
     */
    const EXPIRE_AFTER = 7 * 24 * 60 * 60; // 1 week

    /**
     * The length in characters of verification tokens.
     * @type int
     */
    const TOKEN_LENGTH = 32;

    /**
     * Create a new email verification token.
     * With this token the email address can be verified.
     *
     * The given email address must not have been verified already, or an exception is thrown.
     * This method does not handle verification email sending. This must be done with a different method.
     *
     * @param Email $email The email address to verify.
     * @return EmailVerification The created email verification entry.
     *
     * @throws \Exception Throws if an invalid email address is given, or if the email address is already verified.
     */
    public static function create(Email $email) {
        // Make sure the email address is valid
        if($email == null)
            throw new \Exception('Given email is null');

        // The email address must not be verified already
        if($email->isVerified())
            throw new \Exception('Unable to create email verification entry, email is already verified');

        // Create the entry
        $verification = new EmailVerification();
        $verification->email_id = $email->id;
        $verification->token = self::generateUniqueToken();
        $verification->expire_at = Carbon::now()->addSecond(self::EXPIRE_AFTER);
        $verification->save();

        return $verification;
    }

    /**
     * Check wheher we've recently sent a verification message for this email or
     * if it has already been verified.
     *
     * This only returns false if:
     * - not yet verified
     * - no verification email has been sent
     * - or a verification email is sent but it has already expired
     *
     * If multiple emails are given, this only returns false if the above
     * constraints are false for all emails.
     *
     * @param array(Email)|Email $emails The email or emails.
     * @return bool True if sent, false if not.
     */
    public static function hasSentRecentlyOrVerified($emails) {
        $emails = collect($emails);

        foreach($emails as $email) {
            // Check if already verified
            if($email->isVerified())
                return true;

            // We didn't verify if there are no verifications
            if($email->verifications->isEmpty())
                continue;

            // We did sent recently if any verification is still valid
            $anySent = $email
                ->verifications
                ->contains(function($verification) {
                    return !$verification->isExpired();
                });
            if($anySent)
                return true;
        }

        return false;
    }

    /**
     * Create a new email verification token and send a verification email to the user.
     * With this token the email address can be verified.
     *
     * The given email address must not have been verified already, or an exception is thrown.
     *
     * @param Email $email The email address to verify.
     * @param boolean $justRegistered True if this verification email is send because the user just registered, false if not.
     * @return EmailVerification The created email verification entry.
     *
     * @throws \Exception Throws if an invalid email address is given, or if the email address is already verified.
     */
    public static function createAndSend(Email $email, $justRegistered) {
        // Ensure the email model is saved
        $email->save();

        // Create a verification entry for this email
        $verification = self::create($email);

        // Create the mailable for the verification
        $recipient = new EmailRecipient($email, $email->user);
        $mailable = new Verify($recipient, $verification, $justRegistered);

        // Send the mailable
        Mail::send($mailable);

        return $verification;
    }

    /**
     * Verify the email address corresponding to the given token.
     *
     * @param string $token Email verification token.
     * @return EmailVerifyResult Email verification result.
     */
    public static function verifyToken($token) {
        // The token must not be null
        if(empty($token))
            return new EmailVerifyResult(EmailVerifyResult::ERR_NO_TOKEN);

        // Get the email verification instance, make sure it's valid
        /* @noinspection PhpDynamicAsStaticMethodCallInspection */
        $verification = EmailVerification::where('token', '=', trim($token))->first();
        if($verification == null)
            return new EmailVerifyResult(EmailVerifyResult::ERR_INVALID_TOKEN);
        if($verification->isExpired())
            return new EmailVerifyResult(EmailVerifyResult::ERR_EXPIRED_TOKEN);

        // Get the email address, make sure it isn't already verified
        $email = $verification->email()->firstOrFail();
        if($email->isVerified())
            return new EmailVerifyResult(EmailVerifyResult::ERR_ALREADY_VERIFIED);

        DB::transaction(function() use($email) {
            // Set the verification state of the email address
            $email->verified_at = Carbon::now();
            $email->verified_ip = Request::ip();
            $email->save();

            // Link user to balance import aliases and to alias economy members
            BalanceImportAlias::where('email', $email->email)
                ->update(['user_id' => $email->user_id]);

            // Refresh the economy members for a user
            BalanceImportAlias::refreshEconomyMembersForUser($email->user);
            CommitBalanceUpdatesForUser::dispatch($email->user_id);
        });

        // Return the result
        return new EmailVerifyResult(EmailVerifyResult::OK);
    }

    /**
     * Generate a new and unique verification token.
     * This method blocks until a new unique token has been generated.
     *
     * @return string Unique token.
     */
    private static function generateUniqueToken() {
        // Keep generating tokens until we've an unique one
        do {
            // Generate a new token
            $token = TokenGenerator::generate(self::TOKEN_LENGTH, false);

            // Check whether the token exists
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $exists = EmailVerification::where('token', '=', $token)->first() != null;

        } while($exists);

        // Return the generated token
        return $token;
    }
}
