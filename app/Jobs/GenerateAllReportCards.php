<?php

namespace App\Jobs;

use App\Http\Controllers\ExaminationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Builds a whole school's report cards for one exam in the background and
 * saves the finished page, so the request that asked for it never times out.
 * The result page is exactly what passslipAll() returns; it is just rendered
 * here and served from storage when ready.
 */
class GenerateAllReportCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;
    public int $timeout = 900;

    public function __construct(
        public string $token,
        public int $examId,
        public int $schoolId,
        public array $query = []   // the print options of the original request (lang, template)
    ) {
        $this->onQueue('reports');
    }

    public function handle(): void
    {
        $this->status('running');

        // The report-card code reads the school from the session and options
        // from the request: give this job the same context the web request had.
        $store = app('session.store');
        $store->setId(Str::random(40));
        $store->start();
        $store->put('LoggedSchool', $this->schoolId);

        request()->merge($this->query);

        $response = app(ExaminationController::class)->buildAllSlipsResponse($this->examId, $this->schoolId);

        $html = method_exists($response, 'render')
            ? $response->render()
            : (method_exists($response, 'getContent') ? $response->getContent() : (string) $response);

        $path = "report-cards/{$this->schoolId}/{$this->token}.html";
        Storage::disk('local')->put($path, $html);

        $this->status('done', ['path' => $path]);
    }

    public function failed(\Throwable $e): void
    {
        $this->status('failed', ['error' => Str::limit($e->getMessage(), 200)]);
    }

    private function status(string $status, array $extra = []): void
    {
        Cache::put('reportcards:' . $this->token, [
            'status' => $status,
            'school_id' => $this->schoolId,
            'exam_id' => $this->examId,
        ] + $extra, now()->addHours(6));
    }
}
