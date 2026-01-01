<?php

namespace Ashiful\Upload\Http\Controllers;

use Ashiful\Upload\Facades\SecureUpload;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:'.config('upload.max_size', 10240),
        ]);

        try {
            $path = SecureUpload::upload($request->file('file'), 'uploads');
            $url = SecureUpload::getSignedUrl($path); // Return signed URL by default for immediate preview if needed

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $path = $request->query('path');
        $disk = config('upload.disk', 'public');

        if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk($disk)->download($path);
    }
}
