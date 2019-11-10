<?php

/**
 * Mail related translations.
 */
return [
    'signature' => [
        'caption' => 'Thanks,|That\'s all,',
        'signoff' => '~ :app robot',
    ],

    /**
     * Email emails.
     */
    'email' => [
        /**
         * Email verification email.
         */
        'verify' => [
            'subject' => 'Email verification',
            'subjectRegistered' => ':app registration & email verification',
            'subtitle' => 'You\'re about to verify your email address.',
            'subtitleRegistered' => 'Your account is almost ready.',
            'registered' => 'Thank you for registering an account.',
            'addNewEmail' => 'You\'ve just added a new email address to your account.',
            'verifyBeforeUseAccount' => 'Before you can fully use our service, you need to verify your email address.',
            'verifyBeforeUseEmail' => 'Before you can use it on our service, you need to verify it.',
            'soon' => 'Please do this as soon as possible, the verification link expires **within :expire**.',
            'clickButtonToVerify' => 'Please click the following button to verify your email address.',
            'verifyButton' => 'Verify your email address',
            'manual' => 'If the above button doesn\'t work, open the following link in your web browser:',
        ],

        /**
         * Email verified email.
         */
        'verified' => [
            'subject' => 'Start using :app',
            'subtitle' => 'First of all, welcome to the club!',
            'accountReady' => 'Your email address has just been verified and your account is now ready.',
            'startUsingSeeDashboard' => 'To start using :app, take a look at your personalized dashboard.',
            'configureEmailPreferences' => 'To configure how often you receive email updates from :app, check out your email preferences panel.',
        ]
    ],

    /**
     * Authentication emails.
     */
    // TODO: use :app variable here, instead of Barbapappa
    'auth' => [
        /**
         * Session link email.
         */
        'sessionLink' => [
            'subject' => 'Sign in to Barbapappa',
            'subtitle' => 'Tap the button to sign in to your Barbapappa account.',
            'soon' => 'The link expires **within :expire**, and can be used once.',
            'button' => 'Sign in to Barbapappa',
            'manual' => 'If the above button doesn\'t work, open the following link in your web browser:',
        ],
    ],

    /**
     * Password emails.
     */
    'password' => [
        /**
         * Password request email.
         */
        'request' => [
            'subject' => 'Password reset request',
            'subtitle' => 'We\'ll help you to configure a new password.',
            'requestedReset' => 'You\'ve just requested to reset your password.',
            'visitResetPage' => 'Simply visit the password reset page and enter your preferred password.',
            'soon' => 'Please do this as soon as possible as the reset link expires **within :expire**.',
            'clickButtonToReset' => 'Please click the following button to reset your password.',
            'resetButton' => 'Reset your password',
            'manual' => 'If the above button doesn\'t work, open the following link in your web browser:',
            'notRequested' => 'If you haven\'t requested a password reset, you may ignore this email message.',
        ],

        /**
         * Password reset email.
         */
        'reset' => [
            'subject' => 'Password changed',
            'forSecurity' => 'We\'re just notifying you for security reasons.',
            'useNewPassword' => 'From now on, use your new password to login to your account.',
            'noChangeThenReset' => 'If you didn\'t change your password yourself, please change it as soon as possible using the following link:',
            'orContact' => 'Or [contact](:contact) the :app team as soon as possible about this security incident.',
            'noChangeThenContact' => 'If you received this message but haven\'t changed your password, please [contact](:contact) the :contact team as soon as possible about this security incident.',
        ],

        /**
         * Password disabled email.
         */
        'disabled' => [
            'subject' => 'Password disabled',
            'forSecurity' => 'We\'re just notifying you for security reasons.',
            'noDisabledThenReset' => 'If you didn\'t disable your password yourself, please set a new one as soon as possible using the following link:',
            'orContact' => 'Or [contact](:contact) the :app team as soon as possible about this security incident.',
            'noDisabledThenContact' => 'If you received this message but haven\'t disabled your password, please [contact](:contact) the :contact team as soon as possible about this security incident.',
        ],
    ],

    'payment' => [
        'completed' => [
            'subject' => 'Payment accepted',
            'subtitle' => 'You have topped up your wallet.',
            'paymentReceived' => 'Your payment has been received. It has been processed and accepted.',
            'amountReadyToUse' => 'The amount is now available in your wallet and is ready to be used.',
        ],
        'failed' => [
            'subject' => 'Payment failed',
            'subtitle' => 'Your wallet top up did not succeed.',
            'stateFailed' => 'A payment you\'ve started could not be completed, because it failed. If you believe this is an error, please contact us.',
            'stateRevoked' => 'A payment you\'ve started could not be completed, because it has been revoked. If you believe this is an error, please contact us.',
            'stateRejected' => 'A payment you\'ve started could not be completed, because it has been rejected. If you believe this is an error, please contact us.',
        ],
    ],

    /**
     * Update emails.
     */
    'update' => [
        /**
         * Balance update email.
         */
        'update' => [
            'subject' => 'Balance update for Barbapappa',
            'subtitle' => 'Here is an update for your Barbapappa wallet balances.',
            'pleaseTopUp' => 'Please top up any wallets with a negative balance now, and always make sure you\'ve enough available for the upcoming period.',
            'noUpdateZeroBalance' => 'As soon as the the balance of all wallets is zero, you won\'t receive any further periodic updates.',
        ],
    ],
];
