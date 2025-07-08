<?php

namespace App\Http\Controllers\Api\Attachment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Resources\Attachment\AttachmentResource;
use App\Models\Attachment;
use App\Services\Attachment\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttachmentController extends Controller
{
    private AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     *
     * @param StoreAttachmentRequest $request
     * @return AttachmentResource
     */
    public function store(StoreAttachmentRequest $request): AttachmentResource
    {
        $attachment = $this->attachmentService->store(
            $request->file('file'),
            $request->attachable,
            $request->user()->id
        );

        return new AttachmentResource($attachment);
    }

    public function download(Attachment $attachment)
    {
        $this->authorize('view', $attachment);
        try {
            $filePath = $this->attachmentService->getDownloadableFilePath($attachment);
            return response()->download($filePath, $attachment->file_name);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function destroy(Attachment $attachment): Response
    {
        $this->authorize('delete', $attachment);

        $this->attachmentService->destroy($attachment);

        return response()->noContent();
    }
}
