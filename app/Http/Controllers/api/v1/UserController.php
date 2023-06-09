<?php

namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User; 


class UserController extends Controller
{
    public $successStatus = 200;
    public $invalidStatus = 400;
    public $notfoundStatus = 404;
    public $validationFailed = 401;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [ 
                        'first_name'=> 'required',
                        'last_name'=> 'required',
                        'email'=> 'required|email|unique:users',
                        'password'=> 'required',

                        ]);
        if ($validator->fails())
            { 
                $response=[
                    'message' => $validator->errors()->all()[0],
                    'data' => ''
                ];
              return response()->json($response, $this->invalidStatus);
            }
        $input  = $request->all();
        $input['password']=Hash::make($input['password']);
        if(isset($input['photo']))
        {
            $file = $input['photo']; 
            $original_name=time().'.'.$file->getClientOriginalName();
            $images = env('APP_URL').'/photo/'.$original_name;
            $file->move(public_path('photo'), $original_name); 
            $input['photo']=$images;
        }
        $user = User::create($input);
        if($user){
            $response=[
                    'message' => "Registration complete",
                    'data' => $user
                ];
            return response()->json($response, $this->successStatus);
        }   
        $response=[
                    'message' =>'Error while registration',
                    'data' => ''
                ];
        return response()->json($response, $this->notfoundStatus);  
    }

   

    public function login(Request $request)
    { 
        $validator = Validator::make($request->all(), [ 
            'email'=> 'required|email',
            'password'=> 'required',

            ]);
        if ($validator->fails())
        { 
            $response=[
                'message' => $validator->errors()->all()[0],
                'data' => ''
            ];
        return response()->json($response, $this->invalidStatus);
        }

        $crentials =  [
            'email' => request('email'),
            'password'=>request('password')
        ];
        if(Auth::attempt($crentials)){ 
            $user = Auth::user();
            $success =  $user->createToken('TestTasktokenGenrator')->accessToken; 
            
            $response=[
                'message'=>'Log Success',
                'access_token'=> $success,
                'data'=>$user,
            ];
            return response()->json($response, $this-> successStatus); 
        } 
        else{ 
            return response()->json(['message'=>'Login Failed'], $this->validationFailed); 
        } 
    }
    
    public function profile() 
    { 
        $user = User::where(['id'=>Auth::user()->id])->first(); 
        $response=[
                'message'=>'User profile',
                'data'=>$user
            ];
        return response()->json($response, $this-> successStatus); 
    }
    
   

    public function updateProfile(Request $request) {
        $validator = Validator::make($request->all(), [ 
                       'first_name'=> 'required|max:255',
                       'last_name'=> 'required|max:255',
                       ]);
       if ($validator->fails())
           { 
                $response = [
                    'message'=>$validator->errors()->all()[0],
                    'data'=>''
                ];
                return response()->json($response, 400);            
           }
       $input = $request->all();
       if(isset($input['photo']))
        {
            $file = $input['photo']; 
            $original_name=time().'.'.$file->getClientOriginalName();
            $images = env('APP_URL').'/photo/'.$original_name;
            $file->move(public_path('photo'), $original_name); 
            $input['photo']=$images;
        }
       $user = User::findOrFail(Auth::user()->id);
      
       $status = $user->update($input);
       if($status) {
            $response = [
               'message'=>'Profile updated',
               'data'=>$user
           ];
       }
       else {
           $response = [
               'message'=>'Error while update profile',
               'data'=>''
           ];
       }
       return response()->json($response, $this->successStatus); 
    }


    
}
