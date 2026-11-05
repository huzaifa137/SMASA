<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\ParentAttendanceNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * The parent push for a gate scan, sent by a queue worker so the gate
 * operator never waits on the push service. The notifier writes its own
 * result to parent_notification_logs, exactly as it did inline.
 */
class SendParentAttendanceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 30;
    public array $backoff = [10];

    public function __construct(
        public int $studentId,
        public string $event,
        public array $context = []
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $student = Student::withoutGlobalScopes()->find($this->studentId);

        if (!$student) {
            return;
        }

        ParentAttendanceNotifier::notify($student, $this->event, $this->context);
    }
}
