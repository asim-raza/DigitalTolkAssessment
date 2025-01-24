<?php

namespace DTApi\Http\Controllers;

use App\Enums\HttpStatus;
use App\Enums\ResponseCode;
use DTApi\Http\Requests\JobRequest;
use DTApi\Http\Requests\ReOpenJobRequest;
use DTApi\Http\Requests\StoreJobRequest;
use DTApi\Http\Requests\UpdateJobRequest;
use DTApi\Http\Services\DistanceFeedService;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use DTApi\Repository\DistanceFeedRepository;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $bookingRepository;


    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user_id = $request->get('user_id');
        $response = null;
        $user_type = $request->__authenticatedUser?->user_type;

        if($user_id) {
            $response = $this->bookingRepository->getUsersJobs($user_id);
        } elseif($user_type == config('auth.adminRoleId') || $user_type == config('auth.superAdminRoleId')) {
            $response = $this->bookingRepository->getAll($request);
        }

        //TODO: Job need to be return as JobResource in $reaponse
        if (empty($response)) {
            return response()->json(['data' => [], 'message' => 'No jobs found for the given user'], ResponseCode::NOT_FOUND);
        }

        return response()->json(['data' => $response, 'message' => 'Jobs retrieved successfully'], ResponseCode::OK);

    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->bookingRepository->getJobById($id);

        //TODO: Job need to be return as JobResource
        if (!$job) {
            return response()->json(['data' => null, 'message' => 'Job not found'], ResponseCode::NOT_FOUND);
        }
        return response()->json(['data' => $job, 'message' => null], ResponseCode::OK);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(StoreJobRequest $request, BookingRepository $bookingRepository)
    {
        $data = $request->validated();

        try {
            $storedData = $bookingRepository->store($request->__authenticatedUser, $data);

            return response()->json(['data' => $storedData, 'message' => 'Data stored successfully'], ResponseCode::OK);
        } catch (\Exception $e) {
            return response()->json(['data' => null, 'message' => 'Failed to store data'],  ResponseCode::UNKNOWN_ERROR);
        }

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, UpdateJobRequest $request)
    {
        $validatedData = $request->validated();
        $cuser = $request->__authenticatedUser;

        try {
            $response = $this->bookingRepository->updateJob($id, $validatedData, $cuser);
            return response()->json(['data' => $response,  'message' => 'Data updated successfully'], ResponseCode::OK);
        } catch (\Exception $e) {
            return response()->json(['data' => null, 'message' => 'Failed to update data'], ResponseCode::UNKNOWN_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $data = $request->all();

        try {
            $response = $this->bookingRepository->storeJobEmail($data);
            return response()->json(['data' => $response, 'message' => 'Job email stored '], ResponseCode::OK);
        } catch (\Exception $e) {
            return response()->json(['data' => null, 'message' => 'Failed to store job email'], ResponseCode::UNKNOWN_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(JobRequest $request)
    {
        $data = $request->validated();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->acceptJob($data, $user);

        if($response['status'] == 'success') {
            return response()->json(['data' => $response['list'], 'message' => ''], ResponseCode::OK);
        } else {
            return response()->json(['data' => [], 'message' => $response['message']], ResponseCode::UNKNOWN_ERROR);
        }
    }

    public function acceptJobWithId(JobRequest $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->acceptJobWithId($data, $user);
        if($response['status'] == 'success') {
            return response()->json(['data' => $response['list'], 'message' => $response['message']], ResponseCode::OK);
        } else {
            return response()->json(['data' => [], 'message' => $response['message']], ResponseCode::UNKNOWN_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(JobRequest $request)
    {
        $data = $request->validated();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->cancelJobAjax($data, $user);

        if($response['status'] == 'success') {
            return response()->json(['data' => $response['list'], 'message' => ''], ResponseCode::OK);
        } else {
            return response()->json(['data' => [], 'message' => $response['message']], ResponseCode::UNKNOWN_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingRepository->endJob($data);

        if($response['status'] == 'success') {
            return response()->json(['data' => [], 'message' => ''], ResponseCode::OK);
        } else {
            return response()->json(['data' => [], 'message' => ''], ResponseCode::UNKNOWN_ERROR);
        }

    }

    public function customerNotCall(JobRequest $request)
    {
        $data = $request->validated();

        $response = $this->bookingRepository->customerNotCall($data);

        if($response['status'] == 'success') {
            return response()->json(['data' => [], 'message' => ''], ResponseCode::OK);
        } else {
            return response()->json(['data' => [], 'message' => ''], ResponseCode::UNKNOWN_ERROR);
        }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->getPotentialJobs($user);

        return response()->json(['data' => $response, 'message' => ''], ResponseCode::OK);
    }

    public function distanceFeed(DistanceFeedRequest $request, DistanceFeedService $distanceFeedService)
    {
        $validatedData = $request->validated();
        $distanceFeedService->updateFeed($validatedData);
        return response()->json(['data' => [], 'message' => 'Record updated!'], ResponseCode::OK);

    }

    public function reopen(ReOpenJobRequest $request)
    {
        $data = $request->validated();
        $response = $this->bookingRepository->reopen($data);
        return response()->json(['data' => [], 'message' => $response], ResponseCode::OK);

    }

}
