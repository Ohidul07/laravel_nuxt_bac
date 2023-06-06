<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    // public function __construct() {
    //     $this->middleware('auth:api', ['except' => ['login', 'register']]);
    // }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        //return $this->attemptToLogin($request);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            //return response()->json(['status' => 0] , 200);
            return response()->json(['status' => 0, 'message' => 'Credentials are not correct'] , 200);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            //return response()->json(['status' => 2] , 200);
            return response()->json(['status' => 0, 'message' => 'Credentials are not correct'] , 200);

        }
        return $this->createNewToken($token);
    }

    private function attemptToLogin($request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            //return response()->json($validator->errors(), 422);
            return response()->json(['status' => 0, 'message' => 'Credentials are not correct'] , 200);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            //return response()->json(['error' => 'Unauthorized'], 401);
            return response()->json(['status' => 0, 'message' => 'Credentials are not correct'] , 200);
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $getUser = User::where('email', $request->email)->first();
            $getUser['token'] = $this->createNewToken($token)->original;

            if(isset($getUser)) {
                return response()->json(['status' => 1, 'message' => 'Logged in successfully', 'user_info' => $getUser] , 200);
            }
        }
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
            'password' => 'required|string|confirmed|min:6',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));
        return response()->json([
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
        return response()->json(auth()->user());
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        // return response()->json([
        //     'access_token' => $token,
        //     'token_type' => 'bearer',
        //     'expires_in' => auth()->factory()->getTTL() * 60,
        //     //'user' => auth()->user()
        // ]);

        return response()->json([
            'status' => 1,
            'message' => 'Logged in successfully',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ], 200);
    }
}