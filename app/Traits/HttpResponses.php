<?php 
namespace App\Traits;

trait HttpResponses{
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'status'=>'request was successful',
            'message'=> $message,
            'data'=> $data
        ], $code);
    }
    protected function error($data, $code, $message = null)
    {
        return response()->json([
            'status'=>'error has occurred',
            'message'=> $message,
            'data'=> $data
        ], $code);
    }
}