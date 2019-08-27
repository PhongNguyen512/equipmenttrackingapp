<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\PasswordReset;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;

class ResetPasswordController extends Controller
{

    public function requestResetPassword(Request $request){
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json([
                'message' => 'Your email is not exist in system'
            ], 404);
        }

        $OTP = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'otp' => mt_rand(1000, 9999)
            ]
        );

        if ($user && $OTP)
            $user->notify(
                new PasswordResetRequest($OTP->otp)
            );

        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);
    }

    public function showResetForm($token){

        // $passwordReset = PasswordReset::where('token', $token)->first();

        // if (!$passwordReset)
        //     return response()->json([
        //         'message' => 'This password reset token is invalid.'
        //     ], 404);

        // if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
        //     $passwordReset->delete();
        //     return response()->json([
        //         'message' => 'This password reset token is invalid.'
        //     ], 404);
        // }

        // return view('resetForm');
    }

    public function resetPassword(Request $request){
        // dd($request);
        // $request->validate([
        //     'new_password' => ['required', 'min:8', 'required_with:confirm_password', 'same:confirm_password'],
        //     'confirm_password' => ['min:8', 'same:new_password']
        // ]); 
        // dd("see this?");
    }
}
