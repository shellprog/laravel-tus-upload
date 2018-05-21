<?php

namespace OneOffTech\TusUpload\Concerns;

use Illuminate\Container\Container;
use OneOffTech\TusUpload\TusUpload;
use OneOffTech\TusUpload\Console\TusHookInput;
use Log;
use Exception;

trait ProcessHooks
{
    /**
     * Validates the hook payload
     *
     * Currently checks for the request id, the filename and the token fields
     *
     * @return bool
     */
    private function isPayloadValid($payload){

        \Log::debug('isPayload Valid');

        return $payload->has('MetaData.filename')
               && $payload->has('MetaData.token')
               && !empty($payload->id());
    }

    /**
     * Process the pre-create hook
     */
    private function preCreate(TusHookInput $payload)
    {
        \Log::debug('PRE CREATE');
        $requestId = $payload->id();
        $token = $payload->input('MetaData.token');
        
        $upload = $this->uploads->findByUploadRequestAndToken($requestId, $token);

        \Log::debug("Request ID: ");
        \Log::debug($requestId);
        \Log::debug("Token: ");
        \Log::debug($token);

        if(is_null($upload)){
            Log::info("Upload identified by {$requestId}-{$token} not existing.");
            throw new Exception('Upload not found, continuation not granted');
        }

        \Log::debug('Returning from pre create');

        return true; 
    }
    

    /**
     * Process the post-receive hook
     */
    private function postReceive(TusHookInput $payload)
    {
        \Log::debug('POST RECEIVE');
        $requestId = $payload->id();
        $token = $payload->input('MetaData.token');
        
        $upload = $this->uploads->findByUploadRequestAndToken($requestId, $token);

        // let's update the status of the upload
        $this->uploads->updateProgress($upload, $payload->input('Offset'));
        
        if(is_null($upload->tus_id)){
            $this->uploads->updateTusId($upload, $payload->tusId());
        }

        return true;
    }

    /**
     * Process the post-finish hook
     */
    private function postFinish(TusHookInput $payload)
    {
        \Log::debug('POST FINISH');
        $requestId = $payload->id();
        $token = $payload->input('MetaData.token');
        
        $upload = $this->uploads->findByUploadRequestAndToken($requestId, $token);

        if(is_null($upload)){
            Log::error("Upload {$requestId}-{$token} not found.");
            return false;
        }

        if(is_null($upload->tus_id)){
            $this->uploads->updateTusId($upload, $payload->tusId());
        }

        $this->uploads->complete($upload);

        return true;
    }

    /**
     * Process the post-terminate hook
     */
    private function postTerminate(TusHookInput $payload)
    {
        \Log::debug('POST TERMINATE');
        $requestId = $payload->id();
        $token = $payload->input('MetaData.token');
        
        $upload = $this->uploads->findByUploadRequestAndToken($requestId, $token);

        if(is_null($upload)){
            Log::error("Upload {$requestId}-{$token} not found.");
            return false;
        }

        if(is_null($upload->tus_id)){
            $this->uploads->updateTusId($upload, $payload->tusId());
        }

        $this->uploads->cancel($upload);

        return true;
    }
}