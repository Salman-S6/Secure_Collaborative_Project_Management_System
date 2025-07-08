<?php

namespace App\Services\Attachment;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\FileNotFoundException;

class AttachmentService
{
    /**
     * Store a new file and create a record in the database.
     *
     * @param UploadedFile $file
     * @param mixed $attachable
     * @param int $userId
     * @return Attachment
     */
    public function store(UploadedFile $file, mixed $attachable, int $userId): Attachment
    {
        $path = $file->store('attachments', 'private');

        $attachment = $attachable->attachments()->create([
            'user_id' => $userId,
            'path' => $path,
            'disk' => 'private',
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return $attachment;
    }

    /**
     * Obtain the full path of a file for download after verifying its existence..
     *
     * @param Attachment $attachment
     * @return string
     * @throws FileNotFoundException
     */
    public function getDownloadableFilePath(Attachment $attachment): string
{
    if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
        throw new FileNotFoundException('File not found on disk.');
    }

    return Storage::disk($attachment->disk)->path($attachment->path);
}


    /**
     * Delete a file from storage and its record from the database..
     *
     * @param Attachment $attachment
     * @return void
     */
    public function destroy(Attachment $attachment): void
    {
        if (Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();
    }
}
