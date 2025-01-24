<?php

namespace DTApi\Http\Services;

use DTApi\Helpers\TeHelper;
use DTApi\Repository\BookingRepository;
use DTApi\Repository\DistanceFeedRepository;

class BookingService
{
    /**
     * @var BookingRepository
     */
    protected $bookingRepository;

    public function __construct( BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function store($user, $data) {
        $immediateTime = 5;
        $consumerType = $user->userMeta->consumer_type;

        if ($user->user_type != config('app.appEnv')) {
            return [
                'status' => 'fail',
                'message' => 'Translator can not create booking',
            ];
        }

        $cuser = $user;

        $requiredFields = [
            'from_language_id' => 'Du måste fylla in alla fält',
            'due_date' => 'Du måste fylla in alla fält',
            'due_time' => 'Du måste fylla in alla fält',
            'duration' => 'Du måste fylla in alla fält'
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($data[$field]) && ($field !== 'due_date' || $data['immediate'] == 'no')) {
                return [
                    'status' => 'fail',
                    'message' => $errorMessage,
                    'field_name' => $field,
                ];
            }
        }

        if (!isset($data['customer_phone_type']) && !isset($data['customer_physical_type'])) {
            return [
                'status' => 'fail',
                'message' => 'Du måste göra ett val här',
                'field_name' => 'customer_phone_type',
            ];
        }

// Set customer types
        $data['customer_phone_type'] = isset($data['customer_phone_type']) ? 'yes' : 'no';
        $data['customer_physical_type'] = isset($data['customer_physical_type']) ? 'yes' : 'no';
        $response['customer_physical_type'] = $data['customer_physical_type'];

        if ($data['immediate'] == 'yes') {
            $dueCarbon = Carbon::now()->addMinutes($immediateTime);
            $data['due'] = $dueCarbon->format('Y-m-d H:i:s');
            $data['immediate'] = 'yes';
            $data['customer_phone_type'] = 'yes';
            $response['type'] = 'immediate';
        } else {
            $due = $data['due_date'] . ' ' . $data['due_time'];
            $dueCarbon = Carbon::createFromFormat('m/d/Y H:i', $due);

            if ($dueCarbon->isPast()) {
                return [
                    'status' => 'fail',
                    'message' => "Can't create booking in past",
                ];
            }

            $data['due'] = $dueCarbon->format('Y-m-d H:i:s');
            $response['type'] = 'regular';
        }

// Set job gender and certification
        if (in_array('male', $data['job_for'])) {
            $data['gender'] = 'male';
        } elseif (in_array('female', $data['job_for'])) {
            $data['gender'] = 'female';
        }

        $certifications = [
            'normal' => 'normal',
            'certified' => 'yes',
            'certified_in_law' => 'law',
            'certified_in_helth' => 'health',
        ];

        foreach ($certifications as $key => $value) {
            if (in_array($key, $data['job_for'])) {
                $data['certified'] = $value;
            }
        }

        if (in_array('normal', $data['job_for']) && in_array('certified', $data['job_for'])) {
            $data['certified'] = 'both';
        } elseif (in_array('normal', $data['job_for']) && in_array('certified_in_law', $data['job_for'])) {
            $data['certified'] = 'n_law';
        } elseif (in_array('normal', $data['job_for']) && in_array('certified_in_helth', $data['job_for'])) {
            $data['certified'] = 'n_health';
        }

// Set job type
        $data['job_type'] = match ($consumerType) {
            'rwsconsumer' => 'rws',
            'ngo' => 'unpaid',
            'paid' => 'paid',
            default => 'unknown',
        };

        $data['b_created_at'] = date('Y-m-d H:i:s');

        if (isset($due)) {
            $data['will_expire_at'] = TeHelper::willExpireAt($due, $data['b_created_at']);
        }

        $data['by_admin'] = $data['by_admin'] ?? 'no';

        $job = $cuser->jobs()->create($data);

        $response['status'] = 'success';
        $response['id'] = $job->id;
        $response['job_for'] = [];

        if ($job->gender) {
            $response['job_for'][] = $job->gender === 'male' ? 'Man' : 'Kvinna';
        }

        if ($job->certified) {
            if ($job->certified === 'both') {
                $response['job_for'][] = 'normal';
                $response['job_for'][] = 'certified';
            } else {
                $response['job_for'][] = $job->certified;
            }
        }

        $response['customer_town'] = $cuser->userMeta->city;
        $response['customer_type'] = $cuser->userMeta->customer_type;

        //Event::fire(new JobWasCreated($job, $data, '*'));

//            $this->sendNotificationToSuitableTranslators($job->id, $data, '*');// send Push for New job posting


        return $response;

    }

}