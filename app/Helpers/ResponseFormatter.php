<?php

namespace App\Helpers;

/**
 * Format response.
 */
class ResponseFormatter
{
    /**
     * API Response
     *
     * @var array
     */
    protected static $response = [
        'code' => 200,
        'success' => null,
        'message' => null,
        'data' => null,
    ];

    /**
     * Give success response.
     */
    public static function success($data = null, $message = null)
    {
        self::$response['message'] = $message;
        self::$response['success'] = true;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['code']);
    }

    public static function successWithPagination(
        $data = null,
        $dataName = 'data',
        $message = null,
        $total = null,
        $count = null,
        $perPage = null,
        $currentPage = null,
        $totalPage = null,
    )
    {
        self::$response['message'] = $message;
        self::$response['success'] = true;
        self::$response['data'] = [
            $dataName => $data,
            'count' => $count,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_page' => $totalPage,
        ];

        return response()->json(self::$response, self::$response['code']);
    }

    public static function successWithPaginationV2(
        $data = null,
        $message = null,
        $total = null,
        $count = null,
        $perPage = null,
        $currentPage = null,
        $totalPage = null,
    )
    {
        self::$response['message'] = $message;
        self::$response['success'] = true;
        self::$response['data'] = $data;
        self::$response['pagination'] = [
            'total' => $total,
            'count' => $count,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_page' => $totalPage,
        ];

        return response()->json(self::$response, self::$response['code']);
    }

    /**
     * Give error response.
     */
    public static function error($message = null, $code = 400)
    {
        self::$response['success'] = false;
        self::$response['code'] = $code;
        self::$response['message'] = $message;

        return response()->json(self::$response, self::$response['code']);
    }
}
