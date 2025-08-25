<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WorkflowException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        // Log the workflow exception
        Log::error('Workflow Exception: ' . $this->getMessage(), [
            'exception' => $this,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Workflow error occurred.',
                'message' => $this->getMessage(),
            ], 422);
        }

        return redirect()->back()
            ->with('error', 'Workflow error: ' . $this->getMessage());
    }
}
