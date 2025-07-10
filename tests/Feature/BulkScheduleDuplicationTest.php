<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Trainer;
use App\Models\Category;
use App\Models\Role;
use App\Models\Permission;
use App\Services\TrainerAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class BulkScheduleDuplicationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $trainer;
    protected $category;
    protected $trainerUserId;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);

        // Create trainer user first, then trainer record
        $trainerUser = User::factory()->create([
            'name' => 'Test Trainer',
            'email' => 'trainer@test.com',
        ]);
        
        $this->trainer = Trainer::factory()->create([
            'user_id' => $trainerUser->id,
            'is_active' => true,
        ]);

        // Create category
        $this->category = Category::factory()->create([
            'name' => 'Test Category',
        ]);

        // Create admin role and permissions - simplified approach
        $adminRole = Role::create(['name' => 'Admin', 'title' => 'Admin']);
        $schedulePermission = Permission::create(['name' => 'schedule_access']);
        $adminRole->permissions()->attach($schedulePermission);
        $this->admin->roles()->attach($adminRole);

        // Store trainer user ID for use in schedule creation
        $this->trainerUserId = $trainerUser->id;

        // Mock the notification
        Notification::fake();
    }

    /** @test */
    public function admin_can_access_bulk_duplicate_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.bulk-duplicate.form'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.bulk-duplicate');
        $response->assertSee('Bulk Duplicate Schedules');
    }

    /** @test */
    public function bulk_duplicate_form_shows_recurring_schedules()
    {
        // Create recurring schedules
        $schedule1 = Schedule::factory()->create([
            'title' => 'Test Schedule 1',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $schedule2 = Schedule::factory()->create([
            'title' => 'Test Schedule 2',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(2),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.bulk-duplicate.form'));

        $response->assertStatus(200);
        $response->assertSee('Test Schedule 1');
        $response->assertSee('Test Schedule 2');
    }

    /** @test */
    public function bulk_duplicate_form_excludes_non_recurring_schedules()
    {
        // Create non-recurring schedule
        $nonRecurringSchedule = Schedule::factory()->create([
            'title' => 'Non Recurring Schedule',
            'is_recurring' => false,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(1),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.bulk-duplicate.form'));

        $response->assertStatus(200);
        $response->assertDontSee('Non Recurring Schedule');
    }

    /** @test */
    public function admin_can_duplicate_selected_schedules()
    {
        // Create test schedules
        $schedule1 = Schedule::factory()->create([
            'title' => 'Test Schedule 1',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $schedule2 = Schedule::factory()->create([
            'title' => 'Test Schedule 2',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(2),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule1->id, $schedule2->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'include_inactive' => false,
                'skip_unavailable' => true,
                'duplicate_multiple' => false,
            ]);

        // Debug: Let's see what the response actually contains
        if ($response->status() !== 200) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
        }

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Bulk duplication completed successfully',
        ]);

        // Check that new schedules were created
        $this->assertDatabaseHas('schedules', [
            'title' => 'Test Schedule 1',
            'is_recurring' => true,
        ]);

        $this->assertDatabaseHas('schedules', [
            'title' => 'Test Schedule 2',
            'is_recurring' => true,
        ]);

        // Verify notification was sent
        Notification::assertSentOnDemand(
            \App\Notifications\BulkScheduleDuplicationReport::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'test@example.com';
            }
        );
    }

    /** @test */
    public function bulk_duplicate_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'selected_schedules',
            'target_month',
            'report_email',
        ]);
    }

    /** @test */
    public function bulk_duplicate_validates_selected_schedules_exist()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [99999], // Non-existent ID
                'target_month' => Carbon::now()->addMonth()->format('Y-m'),
                'report_email' => 'test@example.com',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['selected_schedules.0']);
    }

    /** @test */
    public function bulk_duplicate_validates_target_month_format()
    {
        $schedule = Schedule::factory()->create([
            'is_recurring' => true,
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule->id],
                'target_month' => 'invalid-format',
                'report_email' => 'test@example.com',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['target_month']);
    }

    /** @test */
    public function bulk_duplicate_validates_email_format()
    {
        $schedule = Schedule::factory()->create([
            'is_recurring' => true,
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule->id],
                'target_month' => Carbon::now()->addMonth()->format('Y-m'),
                'report_email' => 'invalid-email',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['report_email']);
    }

    /** @test */
    public function bulk_duplicate_creates_schedules_for_multiple_months()
    {
        $schedule = Schedule::factory()->create([
            'title' => 'Test Schedule',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'duplicate_multiple' => true,
                'include_inactive' => false,
                'skip_unavailable' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check that schedules were created for multiple months
        $createdSchedules = Schedule::where('title', 'Test Schedule')
            ->where('id', '!=', $schedule->id)
            ->get();

        $this->assertGreaterThan(1, $createdSchedules->count());
    }

    /** @test */
    public function bulk_duplicate_includes_inactive_schedules_when_requested()
    {
        $inactiveSchedule = Schedule::factory()->create([
            'title' => 'Inactive Schedule',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'inactive',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$inactiveSchedule->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'include_inactive' => true,
                'skip_unavailable' => true,
                'duplicate_multiple' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check that inactive schedule was duplicated
        $this->assertDatabaseHas('schedules', [
            'title' => 'Inactive Schedule',
            'id' => ['!=', $inactiveSchedule->id],
        ]);
    }

    /** @test */
    public function bulk_duplicate_excludes_inactive_schedules_by_default()
    {
        $inactiveSchedule = Schedule::factory()->create([
            'title' => 'Inactive Schedule',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'inactive',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$inactiveSchedule->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'include_inactive' => false,
                'skip_unavailable' => true,
                'duplicate_multiple' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check that no new schedules were created (since inactive was excluded)
        $createdSchedules = Schedule::where('title', 'Inactive Schedule')
            ->where('id', '!=', $inactiveSchedule->id)
            ->get();

        $this->assertEquals(0, $createdSchedules->count());
    }

    /** @test */
    public function bulk_duplicate_preserves_schedule_attributes()
    {
        $originalSchedule = Schedule::factory()->create([
            'title' => 'Test Schedule',
            'description' => 'Test Description',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
            'price' => 50.00,
            'max_participants' => 20,
            'location' => 'Test Location',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$originalSchedule->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'include_inactive' => false,
                'skip_unavailable' => true,
                'duplicate_multiple' => false,
            ]);

        $response->assertStatus(200);

        // Check that duplicated schedule has same attributes (except dates and ID)
        $duplicatedSchedule = Schedule::where('title', 'Test Schedule')
            ->where('id', '!=', $originalSchedule->id)
            ->first();

        $this->assertNotNull($duplicatedSchedule);
        $this->assertEquals('Test Description', $duplicatedSchedule->description);
        $this->assertEquals(50.00, $duplicatedSchedule->price);
        $this->assertEquals(20, $duplicatedSchedule->max_participants);
        $this->assertEquals('Test Location', $duplicatedSchedule->location);
        $this->assertEquals($this->trainer->id, $duplicatedSchedule->trainer_id);
        $this->assertEquals($this->category->id, $duplicatedSchedule->category_id);
    }

    /** @test */
    public function bulk_duplicate_handles_trainer_availability()
    {
        // Mock the trainer availability service
        $this->mock(TrainerAvailabilityService::class, function ($mock) {
            $mock->shouldReceive('getTrainerAvailabilityStatus')
                ->andReturn(['available' => false]);
        });

        $schedule = Schedule::factory()->create([
            'title' => 'Test Schedule',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'include_inactive' => false,
                'skip_unavailable' => true,
                'duplicate_multiple' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check that the duplicated schedule is marked as inactive due to trainer unavailability
        $duplicatedSchedule = Schedule::where('title', 'Test Schedule')
            ->where('id', '!=', $schedule->id)
            ->first();

        $this->assertNotNull($duplicatedSchedule);
        $this->assertEquals('inactive', $duplicatedSchedule->status);
    }

    /** @test */
    public function notification_email_contains_edit_links_and_add_trainer_button()
    {
        // Create fresh trainer and user to avoid test pollution
        $trainerUser = User::factory()->create([
            'name' => 'Fresh Test Trainer',
            'email' => 'fresh.trainer@test.com',
        ]);
        $trainerUser->is_active = true; // dynamic property for test only
        
        $freshTrainer = Trainer::factory()->create([
            'user_id' => $trainerUser->id,
            'is_active' => true,
            'is_available_by_default' => true,
            'default_available_days' => [0,1,2,3,4,5,6], // All days
            'default_start_time' => Carbon::createFromTime(6,0,0),
            'default_end_time' => Carbon::createFromTime(22,0,0),
        ]);

        $targetDate = Carbon::now()->addMonth()->startOfMonth()->addDays(1);
        
        $schedule = Schedule::factory()->create([
            'title' => 'Test Schedule',
            'is_recurring' => true,
            'start_date' => $targetDate,
            'end_date' => $targetDate,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $trainerUser->id, // Use the user ID as trainer_id
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $targetMonth = $targetDate->format('Y-m');

        // Debug: Check trainer availability
        $trainerAvailabilityService = app(\App\Services\TrainerAvailabilityService::class);
        $availabilityStatus = $trainerAvailabilityService->getTrainerAvailabilityStatus(
            $freshTrainer,
            $targetDate,
            $targetDate,
            '09:00:00',
            '10:00:00'
        );
        
        // If trainer is not available, fail with debug info
        if (!$availabilityStatus['available']) {
            $this->fail('Fresh trainer is not available: ' . $availabilityStatus['reason'] . ' | is_available_by_default: ' . var_export($freshTrainer->is_available_by_default, true) . ' | status: ' . json_encode($availabilityStatus));
        }

        // Perform bulk duplication
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule->id],
                'target_month' => $targetMonth,
                'report_email' => 'test@example.com',
                'duplicate_multiple' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify notification was sent and inspect the email content
        Notification::assertSentOnDemand(
            \App\Notifications\BulkScheduleDuplicationReport::class,
            function ($notification, $channels, $notifiable) use ($schedule) {
                $mail = $notification->toMail($notifiable);
                $mailContent = implode("\n", $mail->introLines) . "\n" . implode("\n", $mail->outroLines);
                // Check for edit link
                $editUrl = url('/admin/schedules/' . ($notification->reportData['created_schedules'][0]['id'] ?? 'X') . '/edit');
                $this->assertStringContainsString($editUrl, $mailContent);
                // Check for 'Add a trainer' button
                $this->assertEquals('Add a trainer', $mail->actionText);
                $this->assertEquals(url('/admin/schedules'), $mail->actionUrl);
                return $notifiable->routes['mail'] === 'test@example.com';
            }
        );
    }

    /** @test */
    public function non_admin_users_cannot_access_bulk_duplicate_form()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get(route('admin.schedules.bulk-duplicate.form'));

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_users_cannot_perform_bulk_duplicate()
    {
        $regularUser = User::factory()->create();
        $schedule = Schedule::factory()->create([
            'is_recurring' => true,
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($regularUser)
            ->post(route('admin.schedules.bulk-duplicate'), [
                'selected_schedules' => [$schedule->id],
                'target_month' => Carbon::now()->addMonth()->format('Y-m'),
                'report_email' => 'test@example.com',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function simple_bulk_duplicate_test()
    {
        // Create test schedule
        $schedule = Schedule::factory()->create([
            'title' => 'Test Schedule',
            'is_recurring' => true,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'trainer_id' => $this->trainerUserId,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        // Test the bulk duplication endpoint directly
        $response = $this->post(route('admin.schedules.bulk-duplicate'), [
            'selected_schedules' => [$schedule->id],
            'target_month' => $targetMonth,
            'report_email' => 'test@example.com',
            'include_inactive' => false,
            'skip_unavailable' => true,
            'duplicate_multiple' => false,
        ]);

        // This should fail with 403 (unauthorized) since we're not authenticated as admin
        $response->assertStatus(403);
    }
} 