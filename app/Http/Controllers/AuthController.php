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
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $userRole = strtolower(User::select('*')->where('email', $request->email)
                            ->first()->GetRole()->first()->role);

        $http = new \GuzzleHttp\Client;

        switch( $userRole ){
            case 'admin':
                $scope = 'admin';
                break;
            case 'coordinator':
                $scope = 'coordinator';
                break;
            case 'foremen':
                $scope = 'foremen';
                break;
            default:
                $scope = 'nothing';
                break;
        }

        $response = $http->post(url('/').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->oauth_client->id,
                'client_secret' => $this->oauth_client->secret,
                'username' => $request->email,
                'password' => $request->password,
                'scope' => $scope
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
            'name' => $user->name,
            'role' => $userRole
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

    public function getAppToken(Request $request){
        if( $request->pwa_token === null && $request->app_token === null ){
            return response()->json([
                'message' => 'App Token not found'
            ], 400);
        }else{

            $accessToken = $request->header('Authorization');

            //get user information based on token
            $http = new \GuzzleHttp\Client;
            $user = (object)json_decode((string) $http->request('GET', url('/').'/api/auth/user', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $accessToken,
                ],
            ])->getBody(), true);

            $user = User::find($user->id);

            if( isset($request->app_token) )
                $user->app_token = $request->app_token;
            else if( isset($request->pwa_token) )
                $user->pwa_token = $request->pwa_token;

            $user->save();

            return response()->json([
                'message' => 'Tokens are assigned to user'
            ], 200);    
        }        
    }

}
