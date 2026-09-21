<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Json;
use App\Models\Medias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $media = Media::get();

            return Json::response($media);
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
    public function store2(Request $request)
    {
        $clientId = env('IMGURL_CLINT_ID', 'd265582eabf8d3d'); // Ganti dengan client ID Imgur Anda

        // Mendapatkan file gambar dari permintaan dengan menggunakan method file('nama_field')
        $imageFile = $request->file('media');

        // Mengecek apakah file ada dan valid
        if ($imageFile) {
            // Menyiapkan data untuk dikirimkan
            $formData = [
                'headers' => [
                    'Authorization' => 'Client-ID ' . $clientId,
                ],
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => fopen($imageFile->getRealPath(), 'r'),
                    ],
                ],
            ];

            // Menggunakan Laravel HTTP client untuk melakukan permintaan POST
            $client = new \GuzzleHttp\Client();
            try {
                $response = $client->post('https://api.imgur.com/3/upload', $formData);

                // Mengambil respon dari Imgur
                $responseData = json_decode($response->getBody(), true);

                // Memeriksa respon
                if ($response->getStatusCode() === 200 && isset($responseData['data']['link'])) {
                    // Jika Imgur memberikan URL, Anda dapat menanganinya di sini
                    $imgurUrl = $responseData['data']['link'];
                    $media = new Medias();
                    $media->url = $imgurUrl;
                    $media->type = $request->type;
                    $media->save();
                    return Json::response($media);
                } else {
                    // Jika terdapat masalah dengan respons dari Imgur
                    return response()->json(['error' => 'Gagal mengunggah file ke Imgur.'], 400);
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Tangkap kesalahan dari Guzzle
                return response()->json(['error' => $e->getResponse()->getBody()->getContents()], $e->getCode());
            }
        } else {
            // Jika tidak ada file yang dikirimkan
            return response()->json(['error' => 'Tidak ada file yang dikirimkan.'], 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $attendancesImage = $this->saveImage($request->file('media'), 'media');
            $media = new Medias();
            $media->url = $attendancesImage;
            $media->type = $request->type;
            $media->save();

            return Json::response($media);
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
        $file->move(public_path('media'), $imageName);

        return url('media/' . $imageName);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
