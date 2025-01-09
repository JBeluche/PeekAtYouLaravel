<?php 

namespace App\Traits;


trait HttpResponses{
    
    protected function success($message = null, $data = null,  $code = 200)
    {
        return response()->json(array_filter([
            'status' => 'Request was succesfull',
            'message' => $message,
            'data' => $data,
        ]), $code);
    }

    protected function error($message, $data, $code)
    {
        return response()->json(array_filter([
            'status' => 'Error has occured',
            'message' => $message,
            'data' => $data,
        ]), $code);
    }
}