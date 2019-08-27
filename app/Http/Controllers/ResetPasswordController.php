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

        do{
            $otp = mt_rand(10000, 99999);
        }while( PasswordReset::where('otp', $otp)->first() );

        $OTP = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'otp' => $otp
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

    public function checkOTP(Request $request){

        $otp = PasswordReset::where('otp', $request->otp)->first(); 
        
        if(!$otp){
            return response()->json([
                'message' => 'Your OTP is invalid.'
            ], 404);
        }

        if (Carbon::parse($otp->updated_at)->addMinutes(10)->isPast()) {
            $otp->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        return response()->json([
            'message' => 'OTP check successful.'
        ]);
    }

    public function resetPassword(Request $request){
        // dd($request);
        $request->validate([
            'password' => ['required', 'min:8', 'required_with:confirm_password', 'same:confirm_password'],
            'confirm_password' => ['min:8', 'same:password']
        ]); 
        
        $otp = PasswordReset::where('otp', $request->otp)->first(); 

        if (Carbon::parse($otp->updated_at)->addMinutes(11)->isPast()) {
            $otp->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $otp->email)->first();

        $user->password = bcrypt($request->password);
        $user->save();

        $otp->delete();

        return response()->json([
            'message' => 'Password has been changed successful'
        ]);
    }
}
