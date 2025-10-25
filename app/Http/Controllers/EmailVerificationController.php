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
            return $this->error('', 400, 'Email already verified');
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
            return $this->error('', 404, 'User not found');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('', 400, 'Email already verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return $this->success([
                'message' => 'Email has been verified successfully',
                'verified' => true
            ]);
        }

        return $this->error('', 400, 'Invalid verification link');
    }
} 