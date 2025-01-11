<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AccessKey;
use App\Models\Brand;
use App\Models\Login;
use App\Models\UserHasPermission;
use App\Models\TB_GiverAdmin;
use App\Security\Scope;
use Closure;
use Exception;
use Illuminate\Http\Request;

class GiverRequestMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Closure
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        /*
            Implement, I created it just as an example.
            Implement search in the findByAccessKey table
            Example => AccessKey::findByAccessKey($request->jwtAuth['uid']);
        */

        if ($request->jwtAuth['uid'] != 'c9a3f7cf-73c1-4d7b-812d-45a786b07c97') {
            throw new Exception('Invalid access key for this request!');
        }

        $request->session()->put('user.permission_list', []);
        $request->session()->put('user_id', 1);

        return $next($request);
    }
}
