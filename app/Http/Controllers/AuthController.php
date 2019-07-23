<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\User;

class AuthController extends Controller
{
    public function __construct()
    {
        //get the oauth_client for client_id & client_secret
        $this->oauth_client = DB::table('oauth_clients')
                    ->where('password_client', '=', 1)
                    ->where('revoked', '=', 0)
                    ->first();
    }

    public function login(Request $request)
    {
        // dd( $this->oauth_client );
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $http = new \GuzzleHttp\Client;

        $response = $http->post(url('/').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->oauth_client->id,
                'client_secret' => $this->oauth_client->secret,
                'username' => $request->email,
                'password' => $request->password,
            ],
        ]);

        $user = DB::table('users')
                ->where('email', '=', $request->email)
                ->first();

        // return json_decode((string) $response->getBody(), true);

        return response()->json([
            'token' => json_decode((string) $response->getBody(), true),
            'id' => $user->id,
            'email' => $user->email,            
            'name' => $user->name 
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function refreshToken(Request $request){
        if( !isset($request->refresh_token) )
            return response()->json([
                'message' => 'Refresh Token not found'
            ], 400);

        $http = new \GuzzleHttp\Client;

        $response = $http->post(url('/').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->refresh_token,
                'client_id' => $this->oauth_client->id,
                'client_secret' => $this->oauth_client->secret,
                'scope' => '',
            ],
        ]);
        
        return json_decode((string) $response->getBody(), true);
    }
}