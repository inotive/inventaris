<?php

namespace Tests\Unit\Models;

use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\ReportComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Employee $employee;
    protected DailyReport $report;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->employee = Employee::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->report = DailyReport::create([
            'employee_id' => $this->employee->id,
            'name' => 'Test Report',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_report()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertInstanceOf(DailyReport::class, $comment->report);
        $this->assertEquals($this->report->id, $comment->report->id);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($this->user->id, $comment->user->id);
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $comment->delete();

        $this->assertSoftDeleted('report_comments', [
            'id' => $comment->id,
        ]);

        // Should not appear in normal queries
        $this->assertNull(ReportComment::find($comment->id));

        // Should appear with trashed
        $this->assertNotNull(ReportComment::withTrashed()->find($comment->id));
    }

    /** @test */
    public function user_can_edit_own_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertTrue($comment->canBeEditedBy($this->user));
    }

    /** @test */
    public function user_cannot_edit_other_users_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertFalse($comment->canBeEditedBy($this->otherUser));
    }

    /** @test */
    public function user_can_delete_own_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertTrue($comment->canBeDeletedBy($this->user));
    }

    /** @test */
    public function user_cannot_delete_other_users_comment()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        // otherUser doesn't have reports.delete permission
        $this->assertFalse($comment->canBeDeletedBy($this->otherUser));
    }

    /** @test */
    public function comment_has_correct_fillable_attributes()
    {
        $comment = new ReportComment();

        $this->assertEquals([
            'report_id',
            'user_id',
            'parent_id',
            'content',
        ], $comment->getFillable());
    }

    /** @test */
    public function comment_casts_dates_correctly()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $comment->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $comment->updated_at);
    }

    /** @test */
    public function daily_report_has_many_comments()
    {
        ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Comment 1',
        ]);

        ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Comment 2',
        ]);

        $this->assertCount(2, $this->report->comments);
        $this->assertInstanceOf(ReportComment::class, $this->report->comments->first());
    }

    /** @test */
    public function comments_are_deleted_when_report_is_deleted()
    {
        $comment = ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->report->delete();

        $this->assertDatabaseMissing('report_comments', [
            'id' => $comment->id,
        ]);
    }
}
