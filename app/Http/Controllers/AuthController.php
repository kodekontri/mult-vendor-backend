<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dotenv\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        return "hello";
    }


    public function register(Request $request)
    {
        $user = null;
        $valid = \validator($request->all(),[
            'username' => 'required|unique:users,username',
            'phone' => 'required|unique:users,phone',
            'email' => 'required|email:rfc|unique:users,email',
            'password' => 'required|min:6|max:18'
        ]);

        if ($valid->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
                'error' => $valid->errors()->toArray()
            ], 406);
        }

        try {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);

            if (!$user){
                return response()->json([
                    'status' => false,
                    'message' => 'Account failed to create for some reasons. Please try again',
                    'error' => 'Failed to create account'
                ], 406);
            }

            return response()->json([
                'status' => true,
                'message' => "Account created",
                'data' => [
                    'user_id' => $user->id
                ]
            ], 201);

        }catch (\PDOException $e){
            if($user){
                $user->delete();
            }
            return response()->json([
                'status' => false,
                'message' => 'registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $valid = \validator($request->all(),[
            'email' => 'required',
            'password' => 'required|min:6|max:18'
        ]);

        if ($valid->fails()){
            return response()->json([
                'status' => false,
                'message' => $valid->errors()->toArray(),
                'error' => 'Invalid credentials'
            ], 406);
        }

        $user = User::where('username',$request->email)
            ->orWhere('email', $request->email)->first();

        if (!$user){
            return response()->json([
                'status' => false,
                'message' => 'Account not found',
                'error' => 'invalid credentials'
            ], 406);
        }

        if(!Hash::check($request->password, $user->password)){
            return response()->json([
                'status' => false,
                'message' => 'Password incorrect',
                'error' => 'invalid credentials'
            ], 406);
        }

        try {
            $client = new Client();
            return $client->request('POST', config('service.passport.login_endpoint'),[
                "form_params" => [
                    'client_secret' => config('service.passport.client_secret'),
                    'client_id' => config('service.passport.client_id'),
                    'grant_type' => 'password',
                    'username' => $request->email,
                    'password' => $request->password
                ],
                'header' => [
                    'connection'=>'keep-alive'
                ]
            ]);
        }catch (GuzzleException $e){
            return response()->json([
                'status' => false,
                'message' => 'Login Failed. '.$e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            if (!$user = auth()->user()){
                return response()->json([
                    'status' => true,
                    'message' => 'Account already logged out'
                ]);
            }

            auth()->user()->tokens()->each(function($token){
                $token->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Account has been logged out'
            ]);
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'This action has failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
