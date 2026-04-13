<?php

namespace Tests\Feature\Api;

use App\Models\Report;
use App\Models\Employee;
use App\Models\ReportComment;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Shift;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportCommentApiTest extends TestCase
{
    use WithFaker;

    protected User $user;
    protected Employee $employee;
    protected Report $report;

    protected function setUp(): void
    {
        parent::setUp();

        // Use existing data or create minimal required data
        $this->user = User::first() ?? User::factory()->create();

        // Find or create employee
        $this->employee = Employee::where('user_id', $this->user->id)->first();
        if (!$this->employee) {
            $branch = Branch::first() ?? Branch::create(['name' => 'Test Branch', 'address' => 'Test']);
            $department = Department::first() ?? Department::create(['name' => 'Test Dept', 'branch_id' => $branch->id]);
            $shift = Shift::first();

            $this->employee = Employee::create([
                'user_id' => $this->user->id,
                'name' => $this->user->name,
                'branch_id' => $branch->id,
                'department_id' => $department->id,
                'shift_id' => $shift?->id,
                'gender' => 'Laki-laki',
                'status' => 'Tetap',
                'working_start_date' => now(),
            ]);
        }

        // Create a test report
        $this->report = Report::create([
            'title' => 'API Test Report ' . now()->timestamp,
            'description' => 'Test description',
            'user_id' => $this->user->id,
        ]);

        // Authenticate user
        Sanctum::actingAs($this->user);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        ReportComment::where('report_id', $this->report->id)->forceDelete();
        $this->report->delete();

        parent::tearDown();
    }

    public function test_user_can_get_comments_for_a_report()
    {
        // Create some comments
        $comment1 = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'First test comment',
        ]);

        $comment2 = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Second test comment',
        ]);

        $response = $this->getJson("/api/v1/reports/{$this->report->id}/comments");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Komentar berhasil diambil',
            ]);

        // Cleanup
        $comment1->forceDelete();
        $comment2->forceDelete();
    }

    public function test_user_can_create_a_comment()
    {
        $commentData = [
            'content' => 'This is a new test comment ' . now()->timestamp,
        ];

        $response = $this->postJson("/api/v1/reports/{$this->report->id}/comments", $commentData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan',
            ]);

        // Verify in database
        $this->assertDatabaseHas('report_comments', [
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_create_comment_with_empty_content()
    {
        $response = $this->postJson("/api/v1/reports/{$this->report->id}/comments", [
            'content' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_cannot_create_comment_exceeding_max_length()
    {
        $response = $this->postJson("/api/v1/reports/{$this->report->id}/comments", [
            'content' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_own_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        $response = $this->putJson("/api/v1/reports/{$this->report->id}/comments/{$comment->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Komentar berhasil diperbarui',
            ]);

        // Cleanup
        $comment->forceDelete();
    }

    public function test_user_can_delete_own_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Comment to delete',
        ]);

        $response = $this->deleteJson("/api/v1/reports/{$this->report->id}/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Komentar berhasil dihapus',
            ]);

        // Cleanup
        $comment->forceDelete();
    }

    public function test_user_can_get_single_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Single comment to fetch',
        ]);

        $response = $this->getJson("/api/v1/reports/{$this->report->id}/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Cleanup
        $comment->forceDelete();
    }

    public function test_returns_404_for_non_existent_report()
    {
        $response = $this->getJson("/api/v1/reports/999999/comments");

        $response->assertStatus(404);
    }

    public function test_returns_404_for_non_existent_comment()
    {
        $response = $this->getJson("/api/v1/reports/{$this->report->id}/comments/999999");

        $response->assertStatus(404);
    }
}
