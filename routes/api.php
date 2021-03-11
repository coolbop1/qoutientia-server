<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);   
    Route::post('/edit-profile', [AuthController::class, 'editProfile']);
});
Route::post('/change-pass', [RegisterController::class, 'changePass']);
Route::post('/verifyemail', [RegisterController::class, 'verify']);
Route::get('/plans', [RegisterController::class, 'getPlan']);
Route::post('/add-plan', [RegisterController::class, 'addPlan']);
Route::get('/users', [RegisterController::class, 'getUsers']);
Route::get('/open-users', [RegisterController::class, 'getNonInvestedUsers']);
Route::get('/investments', [RegisterController::class, 'investments']);
Route::get('/withdrawals', [RegisterController::class, 'withdrawals']);
Route::post('/add-investment', [RegisterController::class, 'addInvestment']);