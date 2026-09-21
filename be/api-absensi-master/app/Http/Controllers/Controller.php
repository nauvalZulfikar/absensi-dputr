<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * user_admin (Pengawas) hanya boleh menyentuh divisi yang ditugaskan padanya
     * (lihat User::canManageDivision). 403 envelope sama dgn EnsureRole.
     */
    protected function forbiddenDivision()
    {
        return response()->json(
            ['meta' => ['status' => false, 'message' => 'Forbidden: di luar divisi Anda.', 'code' => 403]],
            403
        );
    }
}
