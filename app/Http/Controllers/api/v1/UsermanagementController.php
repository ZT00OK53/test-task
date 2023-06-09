<?php
namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;
use App\Models\Role;

class UsermanagementController extends Controller
{
    public $successStatus = 200;
    public $invalidStatus = 400;
    public $notfoundStatus = 404;
    public $validationFailed = 401;

    public function index(Request $request){
        $users = new User;
        if (!empty($request->get('name'))) {
            $users =$users->Where('first_name', 'like', '%' . $request->get('name') . '%')->orWhere('email', 'like', '%' . $request->get('name') . '%');
        } 
        $perpage = 10;
        if (!empty($request->perpage)) {
            $perpage = $request->perpage;
        }
        
        if ($request->status == 'active') {
            // Only active
        } 
        elseif ($request->status == 'trashed') {
            $users =$users->onlyTrashed();
        } 
        else {
            $users =$users->withTrashed();
        }
        if($request->sort_by &&  $request->sort_direction){
            $users =$users->orderBy($request->sort_by, $request->sort_direction);
        }
        $response=[
                    'message' => "All Users",
                    'data' => $users->paginate($perpage)
                ];
            return response()->json($response, $this->successStatus);
    }

    
    public function detail($id){
        $user = User::withTrashed()->find($id);
        $response=[
                    'message' => "User Information",
                    'data' => $user
                ];
            return response()->json($response, $this->successStatus);
    }
    
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
        $input['password'] = Hash::make($input['password']);
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
                    'message' => "User created",
                    'data' => $user
                ];
            return response()->json($response, $this->successStatus);
        }   
        $response=[
                    'message' =>'User not found',
                    'data' => ''
                ];
        return response()->json($response, $this->notfoundStatus);  
    }
    
    public function update($id, Request $request){
        $validator = Validator::make($request->all(), [ 
                        'first_name'=> 'required', 
                        'last_name'=> 'required',  
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
        $user = User::withTrashed()->find($id);
        if(isset($input['photo']))
        {
            $file = $input['photo']; 
            $original_name=time().'.'.$file->getClientOriginalName();
            $images = env('APP_URL').'/photo/'.$original_name;
            $file->move(public_path('photo'), $original_name); 
            $input['photo']=$images;
        }
        if($user){
            $user->update($input);
           
            $response=[
                    'message' => "User updated",
                    'data' => $user
                ];
            return response()->json($response, $this->successStatus);
        }   
        $response=[
                    'message' =>'User not found',
                    'data' => ''
                ];
        return response()->json($response, $this->notfoundStatus);  
    }
    
    public function destroy($id)
    {
        $status = User::find($id);       
        $status->delete();
         if($status){
           $response=[
               'message'=>'User disabled',
               'data'=>$status
            ];
           return response()->json($response, $this-> successStatus);
        }
         $response=[
               'message'=>'Error while disabling user',
               'data'=>''
            ];
         return response()->json($response, $this-> ErrorStatus);    
    }
    
    public function restore($id)
    {
        $status = User::withTrashed()->find($id);    
        $status->restore();
         if($status){
          $response=[
               'message'=>'User enabled',
               'data'=>$status
            ];
           return response()->json($response, $this-> successStatus);
        }
            $response=[
               'message'=>'Error while enabling User',
               'data'=>''
            ];
            return response()->json($response, $this-> ErrorStatus);
        
    }
    
}
