<?php

namespace App\Http\Controllers;

use App\Helpers\ValidationDefaults;
use App\Models\Email;
use App\Models\SessionLink;
use App\Services\Auth\AuthResult;
use Illuminate\Http\Request;

class AuthController extends Controller {

    /**
     * Login/register request from the index page.
     *
     * @param Request $request The request.
     */
    public function doContinue(Request $request) {
        // Validate
        $this->validate($request, [
            'email' => 'required|' . ValidationDefaults::EMAIL,
        ]);

        // Find a user with this email address, register if not existant
        $email = Email::where('email', $request->input('email'))->first();
        if(empty($email))
            return redirect('register')
                ->with('email', $request->input('email'))
                ->with('email_lock', true);

        // Create and send session link
        $link = SessionLink::create($email->user);
        $link->sendMail($request->input('email'));

        // Show session link sent page
        return view('myauth.loginSentSession')
            ->with('email', $email)
            ->with('loginWithPassword', $email->user->hasPassword());
    }

    /**
     * Login a user through a session token.
     *
     * @param string $token The session link token used for authentication.
     */
    public function login($token) {
        // Show error if already authenticated
        if(barauth()->isAuth())
            return redirect()
                ->intended(route('dashboard'))
                ->with('success', __('auth.alreadyLoggedIn'));

        // Get the user session link
        $link = SessionLink::notExpired()->where('token', $token)->first();
        if(empty($link))
            return redirect()
                ->route('index')
                ->with('error', __('auth.sessionLinkUnknown'));

        // Consume the authentication link
        $link->consume($token);

        // Redirect to the intended link, from session or session link
        return redirect()
            ->intended($link->intended_url ?? route('dashboard'))
            ->with('success', __('auth.loggedIn'));
    }
}
