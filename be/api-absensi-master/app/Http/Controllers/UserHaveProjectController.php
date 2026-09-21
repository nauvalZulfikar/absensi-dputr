<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\UserHaveProject;
use Illuminate\Http\Request;


class UserHaveProjectController extends Controller
{
    public function deleteUserAssign(Request $request, $id)
    {
        try {
            $data = UserHaveProject::findOrFail($id);
            if (! auth()->user()->canManageProject($data->project_id)) {
                return $this->forbiddenDivision();
            }
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

    public function insertUserAssign(Request $request)
    {
        try {
            if (! auth()->user()->canManageProject($request->project_id)) {
                return $this->forbiddenDivision();
            }
            $data = new UserHaveProject();
            $data->user_id = $request->user_id;
            $data->project_id = $request->project_id;
            $data->type = 'assign';
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

    public function insertUserAssigns(Request $request)
    {
        try {
            if (! auth()->user()->canManageProject($request->project_id)) {
                return $this->forbiddenDivision();
            }
            $userIds = $request->user_ids;
            foreach ($userIds as $key => $userId) {
                $data = new UserHaveProject();
                $data->user_id = $userId;
                $data->project_id = $request->project_id;
                $data->type = 'assign';
                $data->save();
            }

            return Json::response('Sucess');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
