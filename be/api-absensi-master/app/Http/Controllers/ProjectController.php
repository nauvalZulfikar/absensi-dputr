<?php

namespace App\Http\Controllers;

use App\Models\UserHaveProject as UserProject;
use App\Models\Profile;
use App\Models\Project;
use App\Http\Helpers\Json;
use App\Http\Helpers\MethodsHelpers;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->is_my_project ? Auth()->user()->id : null;
            $project = Project::filterByField('devisionId', $request->division_id)
                ->entities($request->entities)
                ->whereDivisions($request->division_ids)
                ->whereWithEntities('users', $request->owner_id, 'user_id')
                ->filterByField('status', $request->status)
                ->filterMyProject($userId)
                ->paginate($request->input('paginate', 10));

            return Json::response($project);
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
            $data = new Project();
            $data->name = $request->name;
            $data->slug = Project::generateSlug($request->name);
            $data->devisionId = $request->devisionId;
            $data->userId = auth()->user()->id;
            $data->projectNo = $request->projectNo;
            $data->startdate = $request->startdate;
            $data->targetdate = $request->targetdate;
            $data->cost = $request->cost;
            $data->status = 'draft';
            $data->rowStatus = false;
            $data->address = $request->address;
            $data->documentId = $request->documentId;
            $data->latitude = $request->latitude;
            $data->longtitude = $request->longitude;
            $data->description = $request->description;
            $data->save();
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
    public function show($id, Request $request)
    {
        try {
            $userId = $request->is_my_project ? Auth()->user()->id : null;
            $data = Project::entities($request->entities)->filterMyProject($userId)->findOrFail($id);
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
            $data = Project::findOrFail($id);
            $data->name = $request->input('name', $data->name);
            $data->slug = Project::generateSlug($request->name);
            $data->startdate = $request->input('startdate', $data->startdate);
            $data->targetdate = $request->input('targetdate', $data->targetdate);
            $data->cost = $request->input('cost', $data->cost);
            $data->status = $request->input('status', $data->status);
            $data->rowStatus = $request->input('rowStatus', $data->rowStatus);
            $data->address = $request->input('address', $data->address);
            $data->latitude = $request->input('latitude', $data->latitude);
            $data->longtitude = $request->input('longtitude', $data->longtitude);
            $data->description = $request->input('description', $data->description);
            $data->physical_process = $request->input("physical_process", $data->physical_process);
            $data->disbursement_of_funds = $request->input("disbursement_of_funds", $data->disbursement_of_funds);
            $data->timeIn = $request->input("timeIn", $data->timeIn);
            $data->timeOut = $request->input("timeOut", $data->timeOut);
            $data->documentId = $request->input('documentId', $data->documentId);
            $data->projectNo = $request->input('projectNo', $data->projectNo);
            $data->save();
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Project::where('id', $id)->delete();
            return Json::response();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function filter(Request $request)
    {
        $data = Project::paginate($request->limit != "" ? $request->limit : 10);

        return $data;
    }

    public function global_function(Request $request)
    {
        try {
            $data = Project::where('devisionId', $request->devisionId)->get();
            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function detailProject(Request $request)
    {
        try {
            $dateNow = Carbon::now()->toDateString();
            $project = Project::where('id', $request->projectId)->first();
            $userId = $project->userId;
            // dd($userId, $dateNow);
            $attendance = Attendance::where('date', $dateNow)
                ->where('userId', $userId)
                ->get();

            $data = [
                'project' => $project,
                'attendance' => $attendance
            ];

            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }
}
