<?php

namespace App\Console\Commands;

use App\Jobs\CompressVideoJob;
use App\Models\ProjectMedia;
use Illuminate\Console\Command;

class CompressLegacyVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:compress-legacy-videos
                            {--quality=720p : Target quality for compression (480p | 720p | 1080p)}
                            {--dry-run : List matched records without dispatching jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find uncompressed / stuck video records and dispatch them through the FFmpeg compression pipeline.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $quality = $this->option('quality');
        $dryRun  = $this->option('dry-run');

        $validQualities = ['480p', '720p', '1080p'];

        if (! in_array($quality, $validQualities, true)) {
            $this->error("Invalid quality \"{$quality}\". Allowed values: " . implode(', ', $validQualities));
            return self::FAILURE;
        }

        // ── Find legacy / stuck video records ─────────────────────────────────
        $videos = ProjectMedia::where('file_type', 'video')
            ->where(function ($query) {
                $query->whereNull('processing_status')
                      ->orWhere('processing_status', '!=', 'ready');
            })
            ->get();

        $total = $videos->count();

        if ($total === 0) {
            $this->info('No uncompressed or stuck video records found. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} video record(s) that need compression.");

        if ($dryRun) {
            $this->warn('[DRY-RUN] No jobs will be dispatched. Matching records:');
            $this->table(
                ['ID', 'file_path', 'processing_status', 'video_quality'],
                $videos->map(fn ($m) => [
                    $m->id,
                    $m->file_path,
                    $m->processing_status ?? 'NULL',
                    $m->video_quality     ?? 'NULL',
                ])->toArray()
            );
            return self::SUCCESS;
        }

        $dispatched = 0;
        $skipped    = 0;

        $this->withProgressBar($videos, function (ProjectMedia $media) use ($quality, &$dispatched, &$skipped) {

            // Determine the source file: prefer the stored original if available
            $sourcePath = $media->original_file_path ?? $media->file_path;

            if (empty($sourcePath)) {
                $this->newLine();
                $this->warn("  [SKIP] Media #{$media->id} has no file path – skipping.");
                $skipped++;
                return;
            }

            // Derive compressed output path in the same directory as the source
            $dir              = ltrim(dirname($sourcePath), '/\\');
            $basename         = pathinfo($sourcePath, PATHINFO_FILENAME);
            $compressedName   = $basename . '-legacy-compressed-' . $media->id . '.mp4';
            $outputRelPath    = $dir . '/' . $compressedName;

            // Resolve final quality: use --quality option, but respect any existing
            // non-null value already stored on the record
            $resolvedQuality = $media->video_quality ?? $quality;

            // ── Update the record before dispatching ──────────────────────────
            $media->processing_status = 'processing';
            $media->video_quality     = $resolvedQuality;
            $media->save();

            // ── Push to the queue ─────────────────────────────────────────────
            CompressVideoJob::dispatch(
                $media->id,
                $sourcePath,
                $outputRelPath,
                $resolvedQuality,
            );

            $dispatched++;
        });

        $this->newLine(2);
        $this->info("Done. Jobs dispatched: {$dispatched}." . ($skipped > 0 ? " Skipped (no path): {$skipped}." : ''));

        return self::SUCCESS;
    }
}
