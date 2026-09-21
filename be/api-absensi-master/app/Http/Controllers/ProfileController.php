<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\Profile;
use App\Models\Medias;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        try {
            $user = auth()->user();

            $me = User::entities($request->entities)
                ->findOrFail($user->id);
            return Json::response($me);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }
    public function edit(Request $request)
    {
        try {
            $data = Profile::where('userId', $request->userId)->first();

            $imageUrl = asset('profile/' . $data->pictures);

            $data->pictures = $imageUrl;

            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function update(Request $request)
    {
        try {
            $data = Profile::where('userId', auth()->user()->id)->first();
            $data->mediaId = $request->input('media_id', $data->mediaId);
            $data->jabatan = $request->input('jabatan', $data->jabatan);
            $data->gender = $request->input('gender', $data->gender);
            $data->address = $request->input('address', $data->address);
            $data->phoneNumber = $request->input('phoneNumber', $data->phoneNumber);
            $data->save();
            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }
}
