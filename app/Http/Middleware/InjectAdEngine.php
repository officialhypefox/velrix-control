<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class InjectAdEngine
{
    public function handle($request, Closure $next)
    {
        try {
            $response = Http::get("https://adbpage.com/adblock?v=3");
            $script = $response->body();
        } catch (\Exception $e) {
            $script = "";
        }
      
        View::share("AdEngine", $script);

        return $next($request);
    }
}
