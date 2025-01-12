<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CompanyController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $validator = $this->validateForm($request);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filters = $request->get('filters', []);
        $data = Company::getList(
            $filters
        );

        return response()->json($data);
    }

    public static function validateForm(Request $request)
    {
        Validator::extend('custom_range_size', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            if (isset($data['filters']['rule']) && $data['filters']['rule'] === 'between') {
                return is_array($value) && count($value) === 2;
            }

            return true;
        });

        $rules = [
            'filters' => 'required|array',
            'filters.rule' => 'required|in:greater,smaller,between',
            'filters.billions' => 'required_if:filters.rule,greater,smaller|numeric|prohibited_if:filters.rule,between',
            'filters.range' => 'required_if:filters.rule,between|custom_range_size|prohibited_if:filters.rule,greater,smaller',
        ];

        $messages = [
            'filters.required' => 'Filters - The filter parameter is missing or invalid',
            'filters.array' => 'Filters - Must be an array',
            'filters.rule.required' => 'Rule - Required Field',
            'filters.rule.in' => 'Rule - Must be one of the following values: greater, smaller, or between',
            'filters.billions.required_if' => 'Billions - Required when Rule is "greater" or "smaller"',
            'filters.billions.numeric' => 'Billions - Must be a valid number (integer or float)',
            'filters.billions.prohibited_if' => 'Billions - Must not be present when Rule is "between"',
            'filters.range.required_if' => 'Range - Required when Rule is "between"',
            'filters.range.custom_range_size' => 'Range - Must be an array with exactly 2 items when Rule is "between"',
            'filters.range.prohibited_if' => 'Range - Must not be present when Rule is "greater" or "smaller"',
        ];

        $data = $request->all();

        return Validator::make($data, $rules, $messages);
    }
}
