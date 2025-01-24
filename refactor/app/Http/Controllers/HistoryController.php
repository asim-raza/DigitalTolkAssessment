<?php

namespace DTApi\Http\Controllers;

use App\Enums\ResponseCode;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class HistoryController extends Controller
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
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->bookingRepository->getUsersJobsHistory($user_id, $request);
            return response()->json(['data' => $response, 'message' => ''], ResponseCode::OK);
        }
        return response()->json(['data' => [], 'message' => ''], ResponseCode::UNKNOWN_ERROR);
    }



}
