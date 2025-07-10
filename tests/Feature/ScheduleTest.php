<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Schedule;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->trainer = Trainer::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_admin_can_create_schedule()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/schedules', [
            'title' => 'Test Schedule',
            'description' => 'Test Description',
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_participants' => 10,
            'price' => 50.00,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/schedules');
        $this->assertDatabaseHas('schedules', [
            'title' => 'Test Schedule',
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_admin_can_update_schedule()
    {
        $this->actingAs($this->admin);
        
        $schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->put("/admin/schedules/{$schedule->id}", [
            'title' => 'Updated Schedule',
            'description' => 'Updated Description',
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'max_participants' => 15,
            'price' => 75.00,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/schedules');
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'title' => 'Updated Schedule',
            'price' => 75.00,
        ]);
    }

    public function test_admin_can_delete_schedule()
    {
        $this->actingAs($this->admin);
        
        $schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->delete("/admin/schedules/{$schedule->id}");

        $response->assertRedirect('/admin/schedules');
        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_schedules_are_filtered_by_expired_status()
    {
        $this->actingAs($this->admin);
        
        // Create active schedule
        $activeSchedule = Schedule::factory()->create([
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // Create expired schedule
        $expiredSchedule = Schedule::factory()->create([
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
            'status' => 'active',
        ]);

        $response = $this->get('/admin/schedules');

        $response->assertStatus(200);
        $response->assertSee($activeSchedule->title);
        $response->assertDontSee($expiredSchedule->title);
    }

    public function test_schedules_are_ordered_by_earliest_first()
    {
        $this->actingAs($this->admin);
        
        $laterSchedule = Schedule::factory()->create([
            'start_date' => now()->addDays(10),
            'start_time' => '10:00',
        ]);

        $earlierSchedule = Schedule::factory()->create([
            'start_date' => now()->addDays(5),
            'start_time' => '09:00',
        ]);

        $response = $this->get('/admin/schedules');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$earlierSchedule->title, $laterSchedule->title]);
    }

    public function test_unlimited_schedule_creation()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/schedules', [
            'title' => 'Unlimited Schedule',
            'description' => 'Unlimited Description',
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'is_unlimited' => true,
            'unlimited_price' => 100.00,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/schedules');
        $this->assertDatabaseHas('schedules', [
            'title' => 'Unlimited Schedule',
            'is_unlimited' => true,
            'unlimited_price' => 100.00,
        ]);
    }

    public function test_schedule_image_upload()
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $file = UploadedFile::fake()->image('schedule.jpg');

        $response = $this->post('/admin/schedules', [
            'title' => 'Schedule with Image',
            'description' => 'Description',
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_participants' => 10,
            'price' => 50.00,
            'status' => 'active',
            'image' => $file,
        ]);

        $response->assertRedirect('/admin/schedules');
        Storage::disk('public')->assertExists('schedules/' . $file->hashName());
    }

    public function test_frontend_schedules_display()
    {
        $schedule = Schedule::factory()->create([
            'trainer_id' => $this->trainer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $response = $this->get('/schedules');

        $response->assertStatus(200);
        $response->assertSee($schedule->title);
    }

    public function test_schedule_validation_errors()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/schedules', [
            'title' => '',
            'trainer_id' => '',
            'category_id' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'trainer_id', 'category_id']);
    }

    public function test_schedule_csv_import()
    {
        $this->actingAs($this->admin);

        $csvContent = "title,description,trainer_id,category_id,start_date,end_date,start_time,end_time,max_participants,price,status\n";
        $csvContent .= "Test Schedule,Test Description,{$this->trainer->id},{$this->category->id},";
        $csvContent .= now()->addDays(1)->format('Y-m-d') . "," . now()->addDays(30)->format('Y-m-d') . ",09:00,10:00,10,50.00,active";

        $file = UploadedFile::fake()->createWithContent('schedules.csv', $csvContent);

        $response = $this->post('/admin/schedules/import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'title' => 'Test Schedule',
        ]);
    }

    public function test_schedule_search_functionality()
    {
        $this->actingAs($this->admin);
        
        $schedule1 = Schedule::factory()->create(['title' => 'Yoga Class']);
        $schedule2 = Schedule::factory()->create(['title' => 'Pilates Class']);

        $response = $this->get('/admin/schedules?search=yoga');

        $response->assertStatus(200);
        $response->assertSee('Yoga Class');
        $response->assertDontSee('Pilates Class');
    }
} 