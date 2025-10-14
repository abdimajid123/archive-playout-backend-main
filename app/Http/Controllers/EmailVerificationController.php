<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    use HttpResponses;

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->error('', 'Email already verified', 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success([
            'message' => 'Verification link sent successfully'
        ]);
    }

    public function verify(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('', 'User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('', 'Email already verified', 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return $this->success([
                'message' => 'Email has been verified successfully',
                'verified' => true
            ]);
        }

        return $this->error('', 'Invalid verification link', 400);
    }
} 