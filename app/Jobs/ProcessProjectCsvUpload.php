<?php

namespace App\Jobs;

use App\Models\ProjectCsvUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProjectCsvUpload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ProjectCsvUpload $upload)
    {
    }

    public function handle(): void
    {
        // TODO: parse the CSV and persist stakeholder data once the target schema is defined.
        $this->upload->update([
            'status' => ProjectCsvUpload::STATUS_PROCESSING,
        ]);

        $this->upload->update([
            'status' => ProjectCsvUpload::STATUS_PENDING,
        ]);
    }
}
