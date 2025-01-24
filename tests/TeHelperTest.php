<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DTApi\Helpers\TeHelper;
use DTApi\Models\Job;
use DTApi\Models\User;
use DTApi\Models\Language;
use DTApi\Models\UserMeta;
use Carbon\Carbon;

class TeHelperTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function fetchLanguageFromJobIdTest()
    {
        $language = Language::factory()->create(['language' => 'English']);

        $fetchedLanguage = TeHelper::fetchLanguageFromJobId($language->id);

        $this->assertEquals('English', $fetchedLanguage);
    }

    /** @test */
    public function getUserMetaValueByKeyTest()
    {
        $user = User::factory()->create();
        UserMeta::factory()->create([
            'user_id' => $user->id,
            'key' => 'notification_preferences',
            'value' => 'email_only'
        ]);

        $metaValue = TeHelper::getUsermeta($user->id, 'notification_preferences');

        $this->assertEquals('email_only', $metaValue);
    }

    /** @test */
    public function convertJobidsToJobObjectTest()
    {
        $jobs = Job::factory()->count(3)->create();

        $jobIds = $jobs->pluck('id')->map(fn($id) => (object) ['id' => $id]);
        $jobObjects = TeHelper::convertJobIdsInObjs($jobIds);

        $this->assertCount(3, $jobObjects);
        $this->assertInstanceOf(Job::class, $jobObjects[0]);
    }

    /** @test */
    public function calculatesCorrectExpiryTimeBasedOnDueTimeTest()
    {
        $createdAt = Carbon::now();
        $dueTime = Carbon::now()->addHours(5);

        // Case 1: Less than 90 hours difference
        $expiryTime = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals($dueTime->format('Y-m-d H:i:s'), $expiryTime);

        // Case 2: Between 24 and 72 hours difference
        $createdAt = Carbon::now()->subHours(25);
        $expiryTime = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals(
            $createdAt->addHours(16)->format('Y-m-d H:i:s'),
            $expiryTime
        );

        // Case 3: Greater than 72 hours difference
        $createdAt = Carbon::now()->subHours(80);
        $expiryTime = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals(
            $dueTime->subHours(48)->format('Y-m-d H:i:s'),
            $expiryTime
        );
    }
}
