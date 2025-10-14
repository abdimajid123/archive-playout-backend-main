<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContentController;

use App\Http\Controllers\Api\ScheduleSlotController;
use App\Http\Controllers\Api\ContentScheduleController;
use App\Http\Controllers\Api\ScheduleGenerationController;
use App\Http\Controllers\Api\ChannelRuleController;
use App\Http\Controllers\Api\RawFileController;
use App\Http\Controllers\Api\ContentCooldownController;


Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'message' => 'Hello World from GitHub Actions - Final Fixed Deployment!',
        'timestamp' => now()->toISOString(),
        'environment' => config('app.env'),
        'version' => '1.0.0'
    ]);
});

// Route::get('/test-db', function () {
//     try {
//         // Test database connection
//         \DB::connection()->getPdo();
        
//         // Test if Content model exists and can be accessed
//         $contentCount = \App\Models\Content::count();
        
//         return response()->json([
//             'status' => 'success',
//             'message' => 'Database connection successful',
//             'content_count' => $contentCount,
//             'database' => config('database.default'),
//             'connection' => config('database.connections.mysql.database')
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Database connection failed',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register')->name('register');
});


Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');


Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');





Route::middleware(['auth:sanctum', 'verified', 'check.token.expiry'])->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])
        ->name('verification.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::middleware('auth:sanctum')->put('/user/update', [AuthController::class, 'updateUser']);


    Route::prefix('contents')->group(function () {
        Route::get('/', [ContentController::class, 'index']);      
        Route::post('/', [ContentController::class, 'store']);     
        Route::put('/{id}', [ContentController::class, 'update']); 
        Route::delete('/{id}', [ContentController::class, 'destroy']); 

    
    });
    

    Route::prefix('rawfiles')->group(function () {
        Route::get('/', [RawFileController::class, 'index']);
        Route::post('/', [RawFileController::class, 'store']);
        Route::get('/{id}', [RawFileController::class, 'search']);
        Route::delete('/{id}', [RawFileController::class, 'destroy']);
        Route::put('/{id}', [RawFileController::class, 'update']);
    });



    Route::middleware('auth:sanctum')->get('/users', [AuthController::class, 'getAllUsers']);
    Route::middleware('auth:sanctum')->put('/users/{id}/role', [AuthController::class, 'updateUserRole']);
    Route::middleware('auth:sanctum')->delete('/users/{id}', [AuthController::class, 'deleteUser']);



    Route::get('content-schedules', [ContentScheduleController::class, 'index']);
    Route::get('content-schedules/{id}', [ContentScheduleController::class, 'show']);
    Route::post('content-schedules', [ContentScheduleController::class, 'store']);
    Route::put('content-schedules/{id}', [ContentScheduleController::class, 'update']);
    Route::delete('content-schedules/{id}', [ContentScheduleController::class, 'destroy']);



    Route::get('schedule-slots', [ScheduleSlotController::class, 'index']);
    Route::post('schedule-slots', [ScheduleSlotController::class, 'store']);
    Route::get('schedule-slots/{id}', [ScheduleSlotController::class, 'show']);
    Route::put('schedule-slots/{id}', [ScheduleSlotController::class, 'update']);
    Route::delete('schedule-slots/{id}', [ScheduleSlotController::class, 'destroy']);



    
    Route::post('channel-rules', [ChannelRuleController::class, 'store']);
    Route::get('channel-rules/{id}', [ChannelRuleController::class, 'show']);
    Route::put('channel-rules/{id}', [ChannelRuleController::class, 'update']);
    Route::delete('channel-rules/{id}', [ChannelRuleController::class, 'destroy']);
    Route::get('channel-rules', [ChannelRuleController::class, 'index']);


    Route::post('schedule/generate', [ScheduleGenerationController::class, 'generate']);
    Route::post('/contents/import', [ContentController::class, 'import']);

    Route::get('cooldowns', [ContentCooldownController::class, 'index']);
    Route::get('cooldowns/{id?}', [ContentCooldownController::class, 'show']); // optional {id}
    Route::put('cooldowns/{id}', [ContentCooldownController::class, 'update']);
    Route::delete('cooldowns/{id}', [ContentCooldownController::class, 'destroy']);

});
















