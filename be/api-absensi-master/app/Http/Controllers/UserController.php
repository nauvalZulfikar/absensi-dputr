<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\RoleHasUser;
use App\Models\User;
use App\Models\UserHaveDivision;
use App\Models\UserHaveRoles;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mail;
use App\Mail\SendMail;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user = User::entities($request->entities)
                ->whereDivisions($request->division_ids)
                ->whereProjects($request->project_ids)
                ->whereHasNotProject($request->not_have_this_projects)
                ->whereHasNotDivisions($request->not_have_divisions)
                ->filterByShift($request->shift_id)
                ->filterSummary($request->summary)
                ->paginate($request->input('paginate', 10));

            return Json::response($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function userSelectionList(Request $request)
    {
        try {
            $user = User::whereDivisions($request->division_ids)
                ->whereRoles($request->roleIds)
                ->entities($request->entities)
                ->whereProjects($request->project_ids)
                ->whereHasNotProject($request->not_have_this_projects)
                ->whereHasNotDivisions($request->not_have_divisions)
                ->filterByShift($request->shift_id)
                ->whereHasNotShift($request->not_have_this_shift)
                ->get();

            return Json::response($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        // try {
        $summary = [
            'all' => 0,
            'active' => 0,
            'not_active' => 0,
            'superadmin' => 0,
            'user' => 0,
            'admin' => 0,
            'supervisor' => 0,
        ];

        $summary['all'] = User::count();
        $summary['active'] = User::where('status', 'active')->count();
        $summary['not_active'] = User::where('status', 'not_active')->count();
        $summary['superadmin'] = User::whereHas('roles', function (Builder $query) {
            $query->where('roleId', 1);
        })->count();
        $summary['user'] = User::whereHas('roles', function (Builder $query) {
            $query->where('roleId', 2);
        })->count();
        $summary['admin'] = User::whereHas('roles', function (Builder $query) {
            $query->where('roleId', 3);
        })->count();
        $summary['supervisor'] = User::whereHas('roles', function (Builder $query) {
            $query->where('roleId', 4);
        })->count();

        return Json::response($summary);
        // } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        //     return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        // } catch (\Illuminate\Database\QueryException $e) {
        //     return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        // } catch (\ErrorException $e) {
        //     return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                "name" => "required|string",
                "username" => "required|string|unique:users,username",
                "email" => "required|string|unique:users,email",
                // "password" => "required|string"
            ]);
            //generate password random 6 charapter
            $password = Str::random(6);

            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            //$user->password = bcrypt($request->password);
            $user->password = bcrypt($password);
            $user->profile_nik = $request->profile_nik;
            $user->status = 'not_active';
            $roleIds = $request->role_ids;
            $user->save();

            foreach ($roleIds as $key => $roleId) {
                $userHaveRole = new RoleHasUser();
                $userHaveRole->userId = $user->id;
                $userHaveRole->roleId = $roleId;
                $userHaveRole->save();
            }

            $mailData = [
                'password' => $password,
                'subject' => 'PASSWORD USER'
            ];

            //Mail::to($request->email)->send(new SendMail($mailData));
            User::sendMail($request->email, $mailData);

            $user->roles;

            DB::commit();
            return Json::response($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            DB::rollBack();
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->name = $request->input('name', $user->name);
            $user->email = $request->input('email', $user->email);
            $user->status = $request->input('status', $user->status);
            $user->profile_nik = $request->input('profile_nik', $user->profile_nik);
            $user->save();

            return Json::response($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        try {
            $userId = Auth()->user()->id;
            $user = User::findOrFail($userId);
            $user->name = $request->input('name', $user->name);
            $user->email = $request->input('email', $user->email);
            $user->password = $request->password ? bcrypt($request->password) : $user->password;
            $user->status = 'active';
            $user->save();
            if (isset($user->profile)) {
                $userProfile = Profile::findOrFail($user->profile->id);
            } else {
                $userProfile = new Profile();
                $userProfile->userId = $user->id;
            }

            $userProfile->name = $request->input('name', $user->name);
            $userProfile->address = $request->input('address', $userProfile->address);
            $userProfile->phoneNumber = $request->input('phoneNumber', $userProfile->phoneNumber);
            $userProfile->gender = $request->input('gender', $userProfile->gender);
            $userProfile->mediaId = $request->input('mediaId', $userProfile->mediaId);
            $userProfile->save();

            return Json::response($userProfile);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }
}
