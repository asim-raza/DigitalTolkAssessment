<?php

namespace DTApi\Http\Controllers;

use App\Enums\ResponseCode;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use DTApi\Http\Requests\NotificationRequest;


/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class NotificationController extends Controller
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


    public function resendNotifications(NotificationRequest $request)
    {
        $data = $request->validated();
        $job = $this->bookingRepository->find($data['jobid']);
        $job_data = $this->bookingRepository->jobToData($job);
        $this->bookingRepository->sendNotificationTranslator($job, $job_data, '*');

        return response()->json(['data' => [], 'message' => 'Push sent!'], ResponseCode::OK);

    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(NotificationRequest $request)
    {
        $data = $request->validated();
        $job = $this->bookingRepository->find($data['jobid']);

        try {
            $this->bookingRepository->sendSMSNotificationToTranslator($job);
            return response()->json(['data' => [], 'message' => 'SMS sent!'], ResponseCode::OK);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'message' => $e->getMessage()], ResponseCode::UNKNOWN_ERROR);
        }
    }

}
