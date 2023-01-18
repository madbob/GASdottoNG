<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Closure;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'mail/status/*',
    ];

    public function handle($request, Closure $next)
    {
        if ($this->isReading($request) || $this->inExceptArray($request) || $this->tokensMatch($request)) {
            return $this->addCookieToResponse($request, $next($request));
        }

        if ($request->ajax()) {
            $ret = (object)[
                'status' => 'error',
                'target' => '',
                'message' => 'Sessione scaduta',
            ];

            return response()->json($ret, 401);
        }
        else {
            return redirect('login');
        }
    }
}
