<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Plan;
use Validator;
use Mail;
use App\Mail\WelcomeMail;
use App\Models\Withdrawals;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|regex:/[0-9]{11}/',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));
        $data = ([
            "name" => $request->name,
            "email" => $request->email,
            "username" => $request->name,
            "phone" => $request->phone,
            ]);
        Mail::to($request->email)->send(new WelcomeMail($data));

        return response([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        $user = auth()->user();
        \Log::info("user here", [$user]);
        $userdata = User::with(['investments' => function($q){
            $q->with('plan')->whereStatus('running')->first();
        },'withdrawals'])->where('id', $user->id)->first();
        return response()->json($userdata);
    }

    public function editProfile(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'phone' => 'required|regex:/[0-9]{11}/',
            'account_type' => 'string|nullable',
            'account_vendor' => 'string|nullable',
            'account_info' => 'string|nullable',
        ]);
        $user = User::find($request->id);
        //\Log::info("edit profile user", [$user]);

        //dd($user->investments);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $availableBal = $request->available_balance ? $request->available_balance + $user->available_balance : $user->available_balance;

        $user->update([
            "name" => $request->name,
            "account_type" => $request->account_type,
            "account_info" => $request->account_info,
            "account_vendor" => $request->account_vendor,
            "email" => $user->email,
            "phone" => $request->phone,
            "book_balance" => $request->book_balance ?? $user->book_balance,
            "available_balance" => $request->debit ? $request->available_balance : $availableBal,
       ]);

       if($request->debit) {
        $withdrawAmount = $user->available_balance - $request->available_balance;
        $request_array = [
            'user_id' => $user->id,
            'amount' => $request->debit,
        ];
        Withdrawals::create($request_array);
       }

       if(!$request->debit){
       $investment = $user->investments()->whereStatus("running")->first();


       if($request->available_balance)
       $updated = $investment->update(["status" => "closed"]);

       }

        return response([
            'message' => 'User successfully updated',
            'user' => $user
        ], 202);
    }

    

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
        ]);
    }

}