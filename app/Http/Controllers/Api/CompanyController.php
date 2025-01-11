<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CompanyController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $filters = $request->get('filters', []);

        $data = Company::getList(
            $filters
        );

        return response()->json($data);
    }
}
