<?php

namespace App\Http\Controllers;

use App\Exports\AttendaceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Helpers\Json;
use App\Models\Attendance;
use App\Models\File;
use App\Models\Medias;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AttendacesController extends Controller
{
    public function summary(Request $request)
    {
        try {
            $data = [
                "all" => 0,
                "clockin" => 0,
                "clockout" => 0,
                "late" => 0,
            ];

            $user_id = $request->admin_mode ? null : auth()->user()->id;
            $projectId = $request->projectId;

            $data['all'] = Attendance::filterByField('projectId', $projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)
                ->whereDateRange('date', $request->since, $request->until)->count();
            $data['clockin'] = Attendance::filterByField('projectId', $projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->filterByField('type', 'clockin')
                ->whereDateRange('date', $request->since, $request->until)->count();
            $data['clockout'] = Attendance::filterByField('projectId', $projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->filterByField('type', 'clockout')
                ->whereDateRange('date', $request->since, $request->until)->count();
            $data['late'] = Attendance::filterByField('projectId', $projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->filterByField('status', 'late')
                ->whereDateRange('date', $request->since, $request->until)->count();
            $data['overtime'] = Attendance::filterByField('projectId', $projectId)->whereDivision($request->division_ids)->filterByField('userId', $user_id)->whereOvertimeShift('lembur')
                ->whereDateRange('date', $request->since, $request->until)->count();

            return Json::response($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function Attendances(Request $request)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'proofOfWork' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'attendances' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'latitude' => 'required',
                'longtitude' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            $ofWorkImage = $this->saveImage($request->file('proofOfWork'), 'proofOfWork');

            // Simpan attendances ke dalam Medias
            $attendancesImage = $this->saveImage($request->file('attendances'), 'attendances');

            $data = Attendance::create([
                'userId' => $request->userId,
                'mediaAttendaceId' => $attendancesImage->id,
                'mediaOfWorkId' => $ofWorkImage->id,
                'projectId' => $request->projectId,
                'latitude' => $request->latitude,
                'longtitude' => $request->longtitude,
                'date' => Carbon::now(),
                'type' => $request->action,
                'time' => Carbon::now()->toTimeString()
            ]);

            // dd($attendance);

            DB::commit();

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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mediaAttendaceId' => 'required',
                'mediaOfWorkId' => 'required',
                'latitude' => 'required',
                'longtitude' => 'required',
                'projectId' => 'required',
                'time' => 'required'
            ]);

            if ($validator->fails()) {
                return Json::response($validator->errors()->toJson(), 400);
            }

            $data = new Attendance();
            $user = auth()->user();
            $data->userId = $user->id;
            $data->mediaAttendaceId = $request->mediaAttendaceId;
            $data->mediaOfWorkId = $request->mediaOfWorkId;
            $data->projectId = $request->projectId;
            $data->latitude = $request->latitude;
            $data->longtitude = $request->longtitude;
            $data->projectId = $request->projectId;
            $data->date = Carbon::now();
            $data->type = $request->action;
            $data->time = $request->time;
            $data->full_address = $request->full_address;
            $data->shift_id = $request->shiftId;

            $shift = Shift::findOrFail($request->shiftId);

            if ($shift) {
                if ($data->type === 'clockin') {
                    $data->status = ($data->date > $shift->timeIn) ? 'late' : 'on-time';
                } else if ($data->type === 'clockout') {
                    $data->status = ($data->date < $shift->timeOut) ? 'too-early' : 'on-time';
                }
            }



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

    public function attendanceLogs(Request $request)
    {
        try {
            $user_id = $request->admin_mode ? null : auth()->user()->id;
            $data = Attendance::entities($request->entities)
                ->filterByField('projectId', $request->projectId)
                ->filterByField('userId', auth()->user()->id)
                ->filterSummary($request->summary, $request, $user_id)
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

    public function Export(Request $request)
    {
        try {
            DB::beginTransaction();
            // Mengambil data dari sumber yang sesuai (misalnya dari method index)
            $user_id = $request->admin_mode ? null : auth()->user()->id;
            $projectId = $request->projectId;
            $attendance = Attendance::entities($request->entities)
                ->whereDivision($request->division_ids)
                ->filterSummary($request->summary, $request, $user_id)
                ->whereDateRange('date', $request->since, $request->until)
                ->paginate($request->input('paginate', 10));

            // Simpan file dengan nama <unik></unik>
            $fileName = 'attendance_' . time() . '.xlsx'; // Nama file unik
            $export = new AttendaceExport($attendance);

            // Menentukan direktori penyimpanan sementara
            $tempStoragePath = 'attendances';

            // Menyimpan file Excel ke dalam direktori penyimpanan sementara
            Excel::store($export, $tempStoragePath . '/' . $fileName);

            // Path sementara ke file yang disimpan
            $tempFilePath = $tempStoragePath . '/' . $fileName;

            $file = new File();
            $file->file_name = $fileName;
            $file->type = 'attendance';
            $file->save();

            // Pindahkan file ke dalam folder public
            $newFilePath = 'attendances/' . $fileName;
            Storage::move($tempFilePath, $newFilePath);

            // Path ke file yang dapat diakses secara langsung oleh klien
            $publicFilePath = asset($newFilePath);

            $data = [
                'file_path' => 'export-data/' . $fileName,
                'file' => $file,
            ];

            // Mengembalikan path file ke klien
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

    public function index(Request $request)
    {
        try {
            $user_id = $request->admin_mode ? null : auth()->user()->id;
            $projectId = $request->projectId;
            $attendance = Attendance::entities($request->entities)
                ->whereDivision($request->division_ids)
                ->filterSummary($request->summary, $request, $user_id)
                ->whereDateRange('date', $request->since, $request->until)
                ->paginate($request->input('paginate', 10));

            return Json::response($attendance);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    private function saveImage($file, $type)
    {
        $imageName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('attendances'), $imageName);

        $media = Medias::create([
            'url' => url('attendances/' . $imageName),
            'type' => $type,
        ]);

        return $media;
    }
}
