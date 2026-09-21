<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\UserHaveDivision;
use Illuminate\Http\Request;

class UserHaveDivisionController extends Controller
{
    public function deleteUserAssign(Request $request, $id)
    {
        try {
            $data = UserHaveDivision::findOrFail($id);
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
            $data = new UserHaveDivision();
            $data->user_id = $request->user_id;
            $data->devision_id = $request->division_id;
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
}
