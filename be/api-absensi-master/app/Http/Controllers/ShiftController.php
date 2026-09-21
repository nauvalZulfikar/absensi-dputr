<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Helpers\Json;
use App\Models\Project;
use App\Models\Shift;
use App\Models\ShiftHaveProject;
use App\Models\ShiftHaveUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $userId = auth()->user()->id;
            // 
            $shift = Shift::filterByProjectId($request->project_id)
                ->filterMyShift($request->is_myshift ? $userId : null)
                ->entities($request->entities)
                ->paginate($request->input('paginate', 10));

            return Json::response($shift);
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
    public function create()
    {
        //
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
            //Query Add Shift
            $data = new Shift();
            $data->timeIn = $request->timeIn;
            $data->timeOut = $request->timeOut;
            $data->type = $request->type;
            $data->startdate = $request->startdate;
            $data->targetdate = $request->targetdate;
            $data->status = '';
            $data->save();


            $projectId = $request->project_id;
            $shiftHaveProject = new ShiftHaveProject();
            $shiftHaveProject->project_id = $projectId;
            $shiftHaveProject->shift_id = $data->id;
            $shiftHaveProject->save();
            // $user_ids = $request->userIds;

            if (isset($project_ids)) {
                foreach ($project_ids as $key => $projectId) {
                    $shiftHaveProject = new ShiftHaveProject();
                    $shiftHaveProject->project_id = $projectId;
                    $shiftHaveProject->shift_id = $data->id;
                    $shiftHaveProject->save();
                }
            }
            // query add user have shift

            // query add shift have project
            DB::commit();
            return Json::response($data);
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

    public function addUserShift(Request $request)
    {
        try {
            $user_ids = $request->user_ids;
            $project_ids = $request->project_ids;
            $data = [];
            if (isset($user_ids)) {
                foreach ($user_ids as $key => $userId) {
                    $shiftHaveUser = new ShiftHaveUser();
                    $shiftHaveUser->user_id = $userId;
                    $shiftHaveUser->shift_id = $request->shift_id;
                    $shiftHaveUser->save();

                    $userData = User::select('shifts.timeIn as timeInShift', 'shifts.timeOut as timeOutShift', 'users.name as nameUser', 'projects.name as projectName', 'users.*', 'projects.*')
                        ->join('user_have_project', 'users.id', '=', 'user_have_project.user_id')
                        ->join('projects', 'user_have_project.project_id', '=', 'projects.id')
                        ->join('shift_have_projects', 'projects.id', '=', 'shift_have_projects.project_id')
                        ->join('shifts', 'shift_have_projects.shift_id', '=', 'shifts.id')
                        ->where('users.id', $userId)
                        ->whereIn('user_have_project.project_id', $project_ids)
                        ->first();

                    $mailData = [
                        'subject' => 'DETAIL INFORMASI PROJECT',
                        'nameUser' => $userData->nameUser,
                        'projectName' => $userData->projectName,
                        'projectNo' => $userData->projectNo,
                        'startdate' => $userData->startdate,
                        'targetdate' => $userData->targetdate,
                        'address' => $userData->address,
                        'timeInShift' => $userData->timeInShift,
                        'timeOutShift' => $userData->timeOutShift
                    ];
                    User::sendMail($userData->email, $mailData);
                }
                array_push($data, $shiftHaveUser);
            }

            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function deleteShiftUser(Request $request)
    {
        try {
            $relation_id = $request->relation_id;
            $data = ShiftHaveUser::findOrFail($relation_id);
            $data->delete();

            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
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
        try {
            $data = Shift::findOrFail($id);
            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show2(Request $request)
    {
        $userData = User::select('users.*', 'projects.*')
            ->join('user_have_project', 'users.id', '=', 'user_have_project.user_id')
            ->join('projects', 'user_have_project.project_id', '=', 'projects.id')
            ->where('users.id', 3)
            ->whereIn('user_have_project.project_id', [2])
            ->get();

        $project_ids = $request->projectIds;
        $user_ids = $request->userIds;

        $data = User::where('id', 3)
            ->whereProjects($project_ids)->get();

        return Json::response($userData);
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

            DB::beginTransaction();

            $data = Shift::findOrFail($id);
            $data->timeIn = $request->input('timeIn', $data->timeIn);
            $data->timeOut = $request->input('timeOut', $data->timeOut);
            $data->save();

            if (isset($user_ids)) {
                foreach ($user_ids as $key => $userId) {
                    $shiftHaveUser = new ShiftHaveUser();
                    $shiftHaveUser->user_id = $userId;
                    $shiftHaveUser->shift_id = $data->id;
                    $shiftHaveUser->save();
                }
            }

            DB::commit();
            return Json::response($data);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $shift = Shift::findOrFail($id);
            $shift->project()->delete();
            $shift->userShift()->delete();
            $shift->projectShift()->delete();
            $shift->delete();

            return Json::response($shift);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }
}
