<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        try {
            $files = File::paginate($request->input('paginate', 10));

            return Json::response($files);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }

    public function downloadExcel($file_path)
    {
        // Periksa apakah file ada
        $tempStoragePath = 'attendances/' . $file_path;
        if (Storage::exists($tempStoragePath)) {
            // Jika file ada, lakukan proses unduh
            return Storage::download($tempStoragePath);
        } else {
            // Jika file tidak ditemukan, tampilkan pesan kesalahan
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }
    }

    public function updateFile(Request $request, $id)
    {
        try {
            $file = File::findOrFail($id);
            $file->file_name = $request->input('file_name', $file->file_name);
            $file->save();

            return Json::response($file);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Json::exception('Error Model ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\Illuminate\Database\QueryException $e) {
            return Json::exception('Error Query ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        } catch (\ErrorException $e) {
            return Json::exception('Error Exception ' . $debug = env('APP_DEBUG', false) == true ? $e : '');
        }
    }
}
