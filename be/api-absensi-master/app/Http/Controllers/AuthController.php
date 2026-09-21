<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\Profile;
use App\Models\RoleHasUser;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;
// use Illuminate\Support\Js;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $userResponseToken = $request->input('g-recaptcha-response');

        // Panggil fungsi untuk memverifikasi reCAPTCHA
        $verificationResult = $this->createAssessment($userResponseToken);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        // Cek apakah email sudah terdaftar
        $user = User::where('email', $credentials['email'])->entities('roles.role,profile.medias,divisions.division')->first();
        if (!$user) {
            return response()->json(['error' => 'Email not registered'], 401);
        }

        // Cek apakah password sesuai
        if (!auth()->attempt($credentials)) {
            return Json::exception("Invalid password");
        }

        $token = auth()->attempt($credentials);

        $data = [
            'token' => $token,
            'user' => $user
        ];

        return $this->createNewToken($data, $verificationResult);
    }

    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:2,100',
                'userName' => 'required|string',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|confirmed|min:6',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            $profile = Profile::create([
                'userId' => $user->id,
                'name' => $request->name,
            ]);

            foreach ($request->roleId as $value) {
                $roleHas = new RoleHasUser();
                $roleHas->userId = $user->id;
                $roleHas->roleId = $value;
                $roleHas->save();
            }

            DB::commit();

            $data = [
                'user' => $user,
                'profile' => $profile
            ];

            return Json::response($data);
            // return response()->json([
            //     'message' => 'User successfully registered',
            //     'user' => $user,
            //     'profile' => $profile
            // ], 200);
        } catch (ValidationException $ex) {
            DB::rollback();
            return redirect()->back()->withErrors($ex->errors());
        }
    }

    public function changePassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'currentPassword' => 'required|string',
                'newPassword' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            // $user = Auth::user();
            $user = auth()->user();
            if (Hash::check($request->currentPassword, $user->password)) {
                $user->update(['password' => bcrypt($request->newPassword)]);
                $user->profile;

                return Json::response([
                    'message' => 'Password successfully updated',
                    'user' => $user,
                ], 200);
            } else {
                return Json::exception(['error' => 'Current password is incorrect'], 400);
            }
        } catch (ValidationException $ex) {
            return response()->json(['error' => 'An error occurred while changing the password'], 500);
        }
    }

    public function logout()
    {
        auth()->logout();
        return Json::response();
        // return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile(Request $request)
    {
        // $user = User::with(['profile', 'role'])->find($request->userId);

        // if (!$user) {
        //     return response()->json(['message' => 'User not found'], 404);
        // }

        // $responseData = [
        //     'name' => $user->name,
        //     'foto' => $user->profile->foto,
        //     'jabatan' => $user->profile->jabatan,
        //     'email' => $user->email,
        //     'roles' => $user->roles ? $user->roles->pluck('name') : [],
        // ];

        // return response()->json(['user' => $responseData]);

        $user = auth()->user();
        $user->load('profile', 'roles');

        $data = [
            'mediaId' => optional($user->profile)->mediaId,
            'jabatan' => optional($user->profile)->jabatan,
            'name' => $user->name,
            'userName' => $user->name,
            'email' => $user->email,
            'url' => optional(optional($user->profile)->medias)->url,
            'role' => $user->role,
        ];

        return Json::response($data);
    }

    protected function createNewToken($token, $result = null)
    {

        $responses = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'message' => 'Login Success',
            'status' => 'success',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'result' => $result
        ];

        return Json::response($responses);
    }

    private function createAssessment(string $token)
    {
        try {
            // Create the reCAPTCHA client.
            $client = new RecaptchaEnterpriseServiceClient();

            // Set the properties of the event to be tracked.
            $event = (new Event())
                ->setSiteKey(env('RECAPTCHA_ENTERPRISE_KEY_ID', '6LdtdXEpAAAAAIWc0qZNIBpGIirR1pjUt05GKWDC')) // Replace with your reCAPTCHA site key
                ->setToken($token);

            // Build the assessment request.
            $assessment = (new Assessment())
                ->setEvent($event);

            // Get the project name from the client.
            $projectName = $client->projectName(env('GOOGLE_CLOUD_PROJECT_ID', 'sibedasabsensi-1707845400236')); // Replace with your Google Cloud Project ID

            // Create assessment.
            $response = $client->createAssessment($projectName, $assessment);

            // Check if the token is valid.
            if ($response->getTokenProperties()->getValid() == false) {
                // Handle invalid token.
                return response()->json(['error' => 'Invalid token']);
            }

            // Check if the expected action was executed.
            if ($response->getTokenProperties()->getAction() == 'LOGIN') {
                // Get the risk score and the reason(s).
                $score = $response->getRiskAnalysis()->getScore();
                return response()->json(['score' => $score]);
            } else {
                // Handle action mismatch.
                return response()->json(['error' => 'Action mismatch']);
            }
        } catch (\Exception $e) {
            // Handle exceptions.
            return response()->json(['error' => $e->getMessage()]);
        } finally {
            // Close the client to free up resources.
            if (isset($client)) {
                $client->close();
            }
        }
    }
}
