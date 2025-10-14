<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Connection;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
{
    $request->validated($request->all());

    if (!\Illuminate\Support\Facades\Auth::attempt($request->only('email', 'password'))) {
        return $this->error('', 'Credentails do not match', 401);
    }

    $user = \App\Models\User::where('email', $request->email)->first();

    // Create Sanctum token (handles abilities correctly)
    $newToken = $user->createToken('API Token of '.$user->name, ['*']);
    $plainTextToken = $newToken->plainTextToken;

    // Set expiry via the model instead of raw DB
    $accessToken = $newToken->accessToken; // Laravel\Sanctum\PersonalAccessToken
    $accessToken->expires_at = now()->addDays(7);
    $accessToken->save();

    return $this->success([
        'user'  => $user,
        'token' => $plainTextToken,
    ]);
}
    


    public function register(StoreUserRequest $request){
        try {
            $request->validated($request->all());

            $user = User::create([
                'name' => $request->name,
                'email'=>$request->email,
                'role'=>$request->role,
                'password'=> Hash::make($request->password),
            ]);

            // Create token first
            $token = $user->createToken('API Token of '. $user->name)->plainTextToken;

            // Try to send email verification, but don't fail if it doesn't work
            try {
                $user->sendEmailVerificationNotification();
                $message = 'Please check your email for verification link';
            } catch (\Exception $e) {
                // Log the error but don't fail the registration
                Log::error('Email verification failed: ' . $e->getMessage());
                $message = 'Registration successful. Email verification may be delayed.';
            }

            return $this->success([
                'user'=> $user,
                'token'=> $token,
                'message' => $message
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (like duplicate email)
            if ($e->getCode() == 23000) { // MySQL duplicate entry error
                return $this->error('', 'Email already exists', 422);
            }
            Log::error('Registration database error: ' . $e->getMessage());
            return $this->error('', 'Registration failed. Please try again.', 500);
            
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return $this->error('', 'Registration failed. Please try again.', 500);
        }
    }


    public function logout(){
        Auth::user()->currentAccessToken()->delete();
        return $this->success([
            'message'=>'you have successfully logged out and your token has been deleted',

        ]);
    }


    public function updateUser(Request $request)
{
    $request->validate([
        'name' => 'required|string|unique:users,name,' . Auth::id(),
        'password' => 'required|string|min:6|confirmed', // Requires 'password_confirmation'
    ]);

    $user = Auth::user(); // Get the authenticated user

    $user->update([
        'name' => $request->name,
        'password' => Hash::make($request->password),
    ]);

    return $this->success([
        'user' => $user,
        'message' => 'User details updated successfully'
    ]);
}

    public function getAllUsers()
    {
    $users = User::all(); // Fetch all users

    return $this->success([
        'users' => $users
    ]);
    }



    public function updateUserRole(Request $request, $id)
{
    // Check if the authenticated user is an admin
    if (Auth::user()->role !== 'admin') {
        return $this->error('', 'Unauthorized: Only admins can change roles.', 403);
    }

    // Validate the request
    $request->validate([
        'role' => 'required|string|in:admin,user,archive,playout' // Add roles as needed
    ]);

    // Find the user
    $user = User::find($id);
    if (!$user) {
        return $this->error('', 'User not found', 404);
    }

    // Update the role
    $user->update(['role' => $request->role]);

    return $this->success([
        'user' => $user,
        'message' => 'User role updated successfully'
    ]);
}




public function deleteUser($id)
{
    // Check if the authenticated user is an admin
    if (Auth::user()->role !== 'admin') {
        return $this->error('', 'Unauthorized: Only admins can delete users.', 403);
    }

    // Find the user
    $user = User::find($id);
    if (!$user) {
        return $this->error('', 'User not found', 404);
    }

    // Delete the user
    $user->delete();

    return $this->success([
        'message' => 'User deleted successfully'
    ]);
}


}

