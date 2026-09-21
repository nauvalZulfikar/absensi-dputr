<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\Devision;
use App\Models\UserHaveDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth()->user()->id;
            $data = Devision::filterByDateRange('created_at', $request->since, $request->until)
                ->entities($request->entities)
                ->filterByField('status', $request->status)
                ->whereDivisions($request->division_ids)
                ->filterMyDivisions($request->isMyDivision ? $userId : null)
                ->paginate($request->input('paginate', 10));

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
            DB::beginTransaction();

            $data = new Devision();
            $data->name = $request->name;
            $data->slug = Devision::generateSlug($request->name);
            $data->description = $request->description;
            $data->status = 'draft';
            $data->save();

            $userHaveDivision = new UserHaveDivision();
            $userHaveDivision->user_id = auth()->user()->id;
            $userHaveDivision->devision_id = $data->id;
            $userHaveDivision->type = "owner";
            $userHaveDivision->save();

            $users = $request->usersIdsAssignTo;

            foreach ($users as $key => $userId) {
                $userAssign = new UserHaveDivision();
                $userAssign->user_id = $userId;
                $userAssign->devision_id = $data->id;
                $userAssign->type = "assign";
                $userAssign->save();
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = Devision::findOrFail($id);
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
            $data = Devision::findOrFail($id);
            $data->name = $request->input("name", $data->name);
            $data->slug = Devision::generateSlug($request->name);
            $data->description = $request->input('description', $data->description);
            $data->status = $request->input('status', $data->status);
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

            Devision::where('id', $id)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'success delete data',
                // 'data' => $data,
            ]);
        } catch (ValidationException $ex) {
            return redirect()->back()->withErrors($ex->errors());
        }
    }

    public function filter(Request $request)
    {
        $data = Devision::paginate($request->limit != "" ? $request->limit : 10);

        return $data;
    }
}
