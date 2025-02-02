<?php

namespace Convoy\Http\Middleware\Client\Server;

use Closure;
use Convoy\Exceptions\Http\Server\ServerStatusConflictException;
use Convoy\Models\Server;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticateServerAccess
{

    /**
     * Routes that this middleware should not apply to if the user is an admin.
     */
    protected array $except = [];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $server = $request->route()->parameter('server');

        if (!$server instanceof Server) {
            throw new NotFoundHttpException('Server not found');
        }

        if ($user->id !== $server->user_id && !$user->root_admin) {
            throw new NotFoundHttpException('Server not found');
        }

        try {
            $server->validateCurrentState();
        } catch (ServerStatusConflictException $exception) {
            if ($request->routeIs('client.servers.show')) {
                return $next($request);
            }

            throw $exception;
        }

        return $next($request);
    }
}
