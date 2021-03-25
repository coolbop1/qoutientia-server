<?php

namespace App\Http\Controllers;
use App\Models\User;
use Mail;
use App\Mail\WelcomeMail;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Investments;
use App\Models\Withdrawals;
use App\Mail\VerifyEmail;

class RegisterController extends Controller
{
    //
    public function verify(Request $request) {

        $code = $this->random_strings(6);
        $user = User::whereEmail($request->vemail)->first();

        if($user) {
            $user->update(["remember_token" => $code]);
            $data = ([
                "code" => $code,
                ]);
            //Mail::to($request->vemail)->send(new VerifyEmail($data));
            return response([
                'message' => 'Verification email sent successfully',
                'code' => $code,
                'user' => $user
            ], 202);
        } else {
            return response([
                'message' => 'User not found',
                'error' => 'error'
            ], 404);
        }
    }

    public function changePass(Request $request) {
        $user = User::where('remember_token', $request->code)->first();
        $updated = $user->update(['password' => bcrypt($request->spassword)]);
        if($updated) {
            return response([
                'message' => 'Password changed successfully',
                'user' => $user
            ], 200);
        } else {
            return response([
                'message' => 'User not found',
                'error' => 'error'
            ], 404);
        }
    }

    public function getPlan() {
        $plans = Plan::get();
        
        return response([
            'message' => 'Plans retrived successfully',
            'plans' => $plans
        ], 200);
    }

   public function random_strings($length_of_string) { 
  
    // String of all alphanumeric character 
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
  
    // Shufle the $str_result and returns substring 
    // of specified length 
    return substr(str_shuffle($str_result),  
                       0, $length_of_string); 
    } 

    public function addPlan(Request $request) {
        $request_array = [
            'name' => $request->name,
            'description' => $request->description,
            'ammount_from' => $request->ammount_from,
            'amount_to' => $request->amount_to,
            'percentage' => $request->percentage,
            'tenure' => $request->tenure
        ];
        $plan = Plan::updateOrCreate(['id' => $request->id],$request_array);

        return response([
            'message' => 'Plan created successfully',
            'plan' => $plan
        ], 201);
    }

    public function addInvestment(Request $request) {
        $plan_percentage = Plan::find($request->plan_id)->percentage;
        $percent = $plan_percentage / 100;
        $roi = $percent * $request->amount;
        $request_array = [
            'user_id' => $request->user_id,
            'plan_id' => $request->plan_id,
            'amount' => $request->amount,
            'roi' => $roi,
        ];

        
        $user = User::find($request->user_id);
        $user_book_balance = $user->book_balance;
        $new_balance = $user_book_balance + $request->amount;

        $new_available_balance = $user->available_balance - $request->amount;

        \Log::info("new_available_balance ".$new_available_balance);
        

        $investment = Investments::updateOrCreate(['id' => $request->id],$request_array);

        if($request->from_balance)
        $user->update(['book_balance' => $request->amount, 'available_balance' => $new_available_balance]);
        else
        $user->update(['book_balance' => $request->amount]);
        


        return response([
            'message' => 'Investment created successfully',
            'investment' => $investment
        ], 201);
    }

    public function investments(Request $request) {
        //$investments = $request->status ? Investments::with('user')->where('status', $request->status)->get() : Investments::get();
        $investments = Investments::with('user')->whereStatus('running')->get();
        
        return response([
            'message' => 'Investments retrived successfully',
            'investments' => $investments
        ], 200);
    }

    public function withdrawals(Request $request) {
        $withdrawals = Investments::with('user')->get();
        
        return response([
            'message' => 'Withdrawals retrived successfully',
            'withdrawals' => $withdrawals
        ], 200);
    }

    public function getNonInvestedUsers(Request $request) {
        $users = User::whereDoesntHave('investments')->get();
        
        return response([
            'message' => 'Users retrived successfully',
            'users' => $users
        ], 200);
    }

    public function getUsers(Request $request) {
        $users = User::with('withdrawals')->get();
        
        return response([
            'message' => 'Users retrived successfully',
            'users' => $users
        ], 200);
    }

    
    
/*
    public function store(Request $request)
    {
        $response = [
            "status" => 201,
            "data" => null,
            "message" => null
        ];

        $emailExist = User::whereEmail($request->email)->count();
        if($emailExist > 0){
            $response["status"] = 400;
            $response["message"] = "Email exist please use another email";

            return response($response, $response["status"]);
        }
        
        $request_array = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password
        ];
        $user = User::create($request_array);

        $data = ([
            "name" => $request->name,
            "email" => $request->email,
            "username" => $request->name,
            "phone" => $request->phone,
            ]);
        Mail::to($request->email)->send(new WelcomeMail($data));

        $response["data"] = $user;
        $response["message"] = "Registration successful";

        return response($response, $response["status"]);
    }

    public function login(Request $request)
    {
        $response = [
            "status" => 200,
            "data" => null,
            "message" => null
        ];

        $user = User::whereEmail($request->email)->whereEmail($request->password)->first();
        if($user){
            $response["data"] = $user;
            $response["message"] = "Login successful";
    
            return response($response, $response["status"]);
        }
        
    }
    */
}
