<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function register(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|min:4',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'phone' => 'required|string|min:4',
            'role' => 'required|in:supervisor,salesman',
            'supervisor_id' => 'nullable|numeric',
        ]);

        $role_id = Role::where('title' , $data['role'])->first()->id;

        $supervisor_id = null;

        if(array_key_exists('supervisor_id', $data)){
            $supervisor = User::find($data['supervisor_id']);
            if(!$supervisor || $supervisor->role->title != 'supervisor'){
                return response()->json(['message' => 'not a supervisor'], 400);
            }
            $supervisor_id = $supervisor->id;
        } 
        
        //if user was an admin, handle it
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
            'role_id' => $role_id,
            'supervisor_id' => $supervisor_id,
        ]);

        event(new Registered($user));
        
        return response()->json([
            'message' => 'User registered successfully',
            //'access_token' => $user->createToken("access token")->plainTextToken,
        ], 201);
    }


    public function login(Request $request) : JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $request->user();

        if(!$user->account_verified_at){
            return response()->json(['message' => 'يرجى تأكيد الحساب من شركة ليليا أولاً'], 400);
        }

        //delete user previous tokens 
        $user->tokens()->delete();
        
        $token = $user->createToken('access token')->plainTextToken;

        return response()->json([
            'user' => $user->id,
            'role' => $user->role->title,
            'access_token' => $token,
        ]);
    }


    public function logout(Request $request) : JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(true);
    }


    public function getMySubs(Request $request) : JsonResponse
    {
        $user = $request->user();
        if($user->role->title != "supervisor"){
            return response()->json('you are not a supervisor', 400);
        }
        
        $subs = $user->subordinates;
        return response()->json(UserResource::collection($subs));
    }


    public function mySupervisor(Request $request) : JsonResponse
    {
        $user = $request->user();
        if($user->role->title != "salesman"){
            return response()->json('you are not a salesman', 400);
        }
        $supervisor = $user->supervisor;
        return response()->json(new UserResource($supervisor));
    }


    public function allSupervisors(Request $request) : JsonResponse
    {
        $user = $request->user();
        $supervisors = User::where("role_id", 2)->get();
        return response()->json(UserResource::collection($supervisors));
    }


    
}
