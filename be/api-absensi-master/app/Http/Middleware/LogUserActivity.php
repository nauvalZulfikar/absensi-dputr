<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogUserActivity
{
    public function handle($request, Closure $next)
    {
        // Tangkap informasi yang ingin Anda log
        $response = $next($request);
        $username = auth()->user() ? auth()->user()->username : 'Guest';
        $timestamp = now();
        $ipAddress = $request->ip();
        $browser = $request->header('User-Agent');
        $url = $request->fullUrl();
        $statusCode = $this->getStatusCode($response);

        // Simpan informasi log ke dalam file log
        Log::info("User: $username, Timestamp: $timestamp, IP: $ipAddress, Browser: $browser, URL: $url, Status Code: $statusCode");

        return $response;
    }

    private function getStatusCode($response)
    {
        if ($response instanceof \Illuminate\Http\Response) {
            return $response->getStatusCode();
        }
        return null;
    }
}
