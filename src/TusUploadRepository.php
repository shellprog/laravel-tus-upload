<?php

namespace Avvertix\TusUpload;

use Avvertix\TusUpload\TusUpload;
use Avvertix\TusUpload\Events\TusUploadStarted;
use Avvertix\TusUpload\Events\TusUploadProgress;
use Avvertix\TusUpload\Events\TusUploadCompleted;
use Avvertix\TusUpload\Events\TusUploadCancelled;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TusUploadRepository
{
    /**
     * Get an upload by the given ID.
     *
     * @param  int  $id
     * @return \Avvertix\TusUpload\TusUpload|null
     */
    public function find($id)
    {
        return TusUpload::find($id);
    }

    /**
     * Get an upload by the given ID.
     *
     * @param  string  $tusId
     * @return \Avvertix\TusUpload\TusUpload|null
     */
    public function findByTusId($tusId)
    {
        return TusUpload::where('tus_id', $tusId)->first();
    }

    /**
     * Get an upload given the user and the request ID.
     *
     * @param  string  $userId
     * @param  string  $requestId
     * @return \Avvertix\TusUpload\TusUpload|null
     */
    public function findByUploadRequest($userId, $requestId)
    {
        return TusUpload::where('user_id', $userId)->where('request_id', $requestId)->first();
    }

    /**
     * Get an upload given the request ID and the upload token.
     *
     * @param  string  $requestId
     * @param  string  $uploadToken
     * @return \Avvertix\TusUpload\TusUpload|null
     */
    public function findByUploadRequestAndToken($requestId, $uploadToken)
    {
        return TusUpload::where('upload_token', $uploadToken)->where('request_id', $requestId)->first();
    }

    /**
     * Get the upload instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection the user uploads queue, ordered by the upload creation date
     */
    public function forUser($userId)
    {
        if($userId instanceof Model){
            $userId = $userId->getKey();
        }

        return TusUpload::where('user_id', $userId)
                        ->orderBy('created_at', 'asc')->get();
    }

    /**
     * Store a new upload.
     *
     * @param  int  $userId The user that is performing the upload
     * @param  string  $requestId The upload request identifier, generated by the javascript library
     * @param  string  $filename The name of the file that is being uploaded 
     * @param  long  $size
     * @param  string  $mimeType
     * @param  long  $offset
     * @param  object|array  $metadata
     * @return \Avvertix\TusUpload\TusUpload
     */
    public function create($user, $requestId, $filename, $size, $mimeType = null, $offset = 0, $metadata = null)
    {
        // todo: add some validation

        $upload = (new TusUpload)->forceFill([
            'user_id' => $user instanceof Model ? $user->getKey() : $user,
            'request_id' => $requestId,
            'filename' => $filename,
            'size' => $size,
            'offset' => $offset,
            'mimetype' => $mimeType,
            'metadata' => $metadata,
            'upload_token' => str_random(60 - strlen($requestId)) . $requestId,
            'upload_token_expires_at' => Carbon::now()->addHour(),
        ]);

        $upload->save();

        event(new TusUploadStarted($upload));

        return $upload;
    }

    /**
     * Update the given upload upload progress.
     *
     * The update is performed only if the upload is not completed or cancelled
     *
     * @param  TusUpload  $upload
     * @param  int  $offset The new transferred bytes offset
     * @return \Avvertix\TusUpload\TusUpload
     */
    public function updateProgress(TusUpload $upload, $offset)
    {
        if($upload->completed() || $upload->cancelled()){
            return $upload;
        }

        $upload->forceFill([
            'offset' => $offset,
        ])->save();

        event(new TusUploadProgress($upload));

        return $upload;
    }

    /**
     * Update the given upload with the tus generated identifier.
     *
     * The update is performed only if the upload is not completed or cancelled
     *
     * @param  TusUpload  $upload
     * @param  string  $tusId The tus generated identifier for the upload
     * @return \Avvertix\TusUpload\TusUpload
     */
    public function updateTusId(TusUpload $upload, $tusId)
    {
        if($upload->completed() || $upload->cancelled()){
            return $upload;
        }

        $upload->forceFill([
            'tus_id' => $tusId,
        ])->save();

        return $upload;
    }

    /**
     * Delete the given upload. The operation cannot be undone.
     *
     * @param  \Avvertix\TusUpload\TusUpload  $upload
     * @return boolean true if the upload was deleted, false otherwise
     */
    public function delete(TusUpload $upload)
    {
        if(!$upload->completed() && !$upload->cancelled()){
            return false;
        }

        $upload->delete();

        return true;
    }

    /**
     * Mark the given upload cancelled.
     *
     * @param  TusUpload  $upload
     * @return \Avvertix\TusUpload\TusUpload
     */
    public function cancel(TusUpload $upload)
    {
        $upload->forceFill([
            'cancelled' => true,
        ])->save();

        event(new TusUploadCancelled($upload));

        return $upload;
    }

    /**
     * Mark the given upload as completed.
     *
     * @param  TusUpload  $upload
     * @param  string  $name
     * @param  string  $redirect
     * @return \Avvertix\TusUpload\TusUpload
     */
    public function complete(TusUpload $upload)
    {
        $upload->forceFill([
            'completed' => true,
            'offset' => $upload->size
        ])->save();

        event(new TusUploadCompleted($upload));

        return $upload;
    }
}