<?php

namespace DTApi\Http\Services;

use DTApi\Repository\BookingRepository;
use DTApi\Repository\DistanceFeedRepository;

class DistanceFeedService
{
    /**
     * @var DistanceFeedRepository
     */
    protected $distanceFeedRepository;
    /**
     * @var BookingRepository
     */
    protected $bookingRepository;

    public function __construct(DistanceFeedRepository $distanceFeedRepository, BookingRepository $bookingRepository)
    {
        $this->distanceFeedRepository = $distanceFeedRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function updateFeed($data) {

        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $jobId = $data['jobid'];

        if ($distance || $time) {
            $this->distanceFeedRepository->updateDistance($jobId, [
                'distance' => $distance,
                'time' => $time,
            ]);
        }

        $this->bookingRepository->updateJobWithId($jobId, [
            'admin_comments' => $data['admincomment'] ?? '',
            'flagged' => $data['flagged'] ? 'yes' : 'no',
            'session_time' => $data['session_time'] ?? '',
            'manually_handled' => $data['manually_handled'] ? 'yes' : 'no',
            'by_admin' => $data['by_admin'] ? 'yes' : 'no',
        ]);

    }

}