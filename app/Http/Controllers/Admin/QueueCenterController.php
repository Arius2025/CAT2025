<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueueCenterController extends Controller
{
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:generate,categorize,audit',
            'job_id' => 'required|string|min:8|max:80',
        ]);

        $type = (string) $validated['type'];
        $jobId = trim((string) $validated['job_id']);

        // Delete queued jobs for this jobId (we embed jobId inside payload arguments).
        $needle = '%' . $jobId . '%';
        $deletedJobs = (int) DB::table('jobs')->where('payload', 'like', $needle)->delete();
        $deletedFailed = (int) DB::table('failed_jobs')->where('payload', 'like', $needle)->delete();

        // Mark cache status as canceled so UI can reflect it.
        if ($type === 'generate') {
            $key = 'ai_generate:' . $jobId;
            $data = Cache::get($key);
            if (!is_array($data)) $data = [];
            $data['status'] = 'canceled';
            $data['message'] = 'Dibatalkan oleh user.';
            Cache::put($key, $data, now()->addHour());
        } elseif ($type === 'categorize') {
            $key = 'ai_categorize:' . $jobId;
            $data = Cache::get($key);
            if (!is_array($data)) $data = [];
            $data['status'] = 'canceled';
            $data['message'] = 'Dibatalkan oleh user.';
            $data['last_error'] = $data['last_error'] ?? null;
            Cache::put($key, $data, now()->addHour());
        } else { // audit
            $base = 'ai_audit:' . $jobId;
            Cache::put($base . ':status', 'canceled', now()->addHour());
            Cache::put($base . ':message', 'Dibatalkan oleh user.', now()->addHour());
            Cache::put($base . ':updated_at', now()->toISOString(), now()->addHour());
        }

        return response()->json([
            'ok' => true,
            'deleted' => [
                'jobs' => $deletedJobs,
                'failed_jobs' => $deletedFailed,
            ],
            'type' => $type,
            'job_id' => $jobId,
        ]);
    }
}

