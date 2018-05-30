<?php

namespace OneOffTech\TusUpload\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use OneOffTech\TusUpload\Concerns\ProcessHooks;
use OneOffTech\TusUpload\Events\TusUploadCancelled;
use OneOffTech\TusUpload\Http\Requests\CreateUploadRequest;
use OneOffTech\TusUpload\TusUploadRepository;

class TusUploadQueueController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ProcessHooks;

    /**
     * @var \OneOffTech\TusUpload\TusUploadRepository
     */
    private $uploads = null;

    public function __construct(TusUploadRepository $uploads)
    {
        $this->uploads = $uploads;
    }

    public function handle()
    {

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        $hook = $_SERVER['HTTP_HOOK_NAME'];

        $payloadString = $inputJSON;

        //Log::debug($payloadString);
        $payload = TusHookInput::create($payloadString);

        //Log::info("Processing $hook...", ['payload' => $payload]);

        if (!in_array($hook, ['pre-create', 'post-finish', 'post-terminate', 'post-receive'])) {
            throw new Exception("Unrecognized hook {$hook}");
        }

        if (is_null(!$payload)) {
            throw new Exception('Payload parsing error');
        }

        if (!$this->isPayloadValid($payload)) {
            throw new Exception('Invalid payload');
        }

        $done = $this->{camel_case($hook)}($payload);

        //Log::info("$hook processed.", ['payload' => $payload, 'done' => $done]);

        return $done ? 0 : 1;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        return response()->json($this->uploads->forUser($request->user()));
    }


    /**
     * Creates an entry in the upload queue.
     *
     * Checks if the user can do the upload and returns the token
     * for the real upload
     *
     * @param  OneOffTech\TusUpload\Http\Requests\CreateUploadRequest $request
     * @return \Illuminate\Http\Response|OneOffTech\TusUpload\TusUpload
     */
    public function store(CreateUploadRequest $request)
    {

        $upload = $this->uploads->create(
            $request->user(),
            $request->input('id'),
            $request->input('name'),
            (int)$request->input('size'),
            $request->input('type', null),
            0,
            $request->except(['id', 'name', 'size', 'type']));


        $data = [
            'request_id' => $upload->request_id,
            'upload_token' => $upload->upload_token,
            'name' => $upload->filename,
            'size' => $upload->size,
            'location' => tus_url(),
        ];

        return response()->json($data);

    }

    /**
     * Remove the specified resource from storage.
     *
     * It can be done only if the upload is terminated (either
     * because completed or cancelled)
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $upload)
    {

        $cancelled_upload = $this->uploads->cancel($this->uploads->findByUploadRequest($request->user(), $upload));

        event(new TusUploadCancelled($cancelled_upload));

        return response()->json($cancelled_upload);

    }
}
