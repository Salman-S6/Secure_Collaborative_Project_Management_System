<?php

namespace App\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'status' => 'fail',
            'message' => $this->getMessage() ?: 'File not found on disk.'
        ], 404);
    }
}
