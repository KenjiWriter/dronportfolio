<?php

namespace App\Jobs;

use App\Models\ProjectMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;

class CompressVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry up to 3 total attempts before marking as permanently failed.
     */
    public int $tries = 3;

    /**
     * Allow up to 90 minutes for very large source files.
     */
    public int $timeout = 5400;

    // ─── Resolution / bitrate look-up table ──────────────────────────────────

    private const RESOLUTIONS = [
        '1080p' => ['width' => 1920, 'height' => 1080, 'video_kbps' => 4000, 'audio_kbps' => 192],
        '720p'  => ['width' => 1280, 'height' => 720,  'video_kbps' => 2500, 'audio_kbps' => 128],
        '480p'  => ['width' => 854,  'height' => 480,  'video_kbps' => 1000, 'audio_kbps' => 96],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param int    $mediaId          ProjectMedia primary key
     * @param string $sourceRelPath    Path relative to public_folder disk root (e.g. "Projekty/portfolio/slug/99-ROBOCZE/raw.mp4")
     * @param string $outputRelPath    Relative path for the compressed output file (e.g. "Projekty/…/compressed.mp4")
     * @param string $quality          '1080p' | '720p' | '480p'
     */
    public function __construct(
        private readonly int    $mediaId,
        private readonly string $sourceRelPath,
        private readonly string $outputRelPath,
        private readonly string $quality,
    ) {}

    public function handle(): void
    {
        $media = ProjectMedia::find($this->mediaId);

        if (! $media) {
            Log::error("CompressVideoJob: ProjectMedia #{$this->mediaId} not found – aborting.");
            return;
        }

        $settings = self::RESOLUTIONS[$this->quality] ?? self::RESOLUTIONS['720p'];
        $w        = $settings['width'];
        $h        = $settings['height'];

        try {
            Log::info("CompressVideoJob: Encoding media #{$this->mediaId} at {$this->quality} → {$this->outputRelPath}");

            // H.264 video + AAC audio, targeted bitrates
            $format = (new X264('aac', 'libx264'))
                ->setKiloBitrate($settings['video_kbps'])
                ->setAudioKiloBitrate($settings['audio_kbps']);

            // Ensure the output directory exists inside the public folder
            File::ensureDirectoryExists(
                public_path(dirname($this->outputRelPath)),
                0755,
                true
            );

            // Scale down to fit within the target bounding box while:
            //   • preserving aspect ratio  (min ratio)
            //   • keeping dimensions divisible by 2 (H.264 requirement)
            FFMpeg::fromDisk('public_folder')
                ->open($this->sourceRelPath)
                ->addFilter(function ($filters) use ($w, $h) {
                    $filters->custom(
                        "scale=iw*min({$w}/iw\\,{$h}/ih):trunc(ow/a/2)*2,setsar=1"
                    );
                })
                ->export()
                ->toDisk('public_folder')
                ->inFormat($format)
                ->save($this->outputRelPath);

            Log::info("CompressVideoJob: Done → {$this->outputRelPath}");

            // Swap to the compressed file and mark as ready
            $media->update([
                'file_path'         => $this->outputRelPath,
                'processing_status' => 'ready',
            ]);

            // Remove the raw original to free disk space
            $rawAbsolute = public_path($this->sourceRelPath);
            if (File::exists($rawAbsolute)) {
                File::delete($rawAbsolute);
            }

        } catch (\Throwable $e) {
            Log::error("CompressVideoJob: Compression FAILED for media #{$this->mediaId}: {$e->getMessage()}");

            $media->update(['processing_status' => 'failed']);

            throw $e; // Allow queue to retry / record the failure
        }
    }

    /**
     * Called after all retry attempts are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        ProjectMedia::find($this->mediaId)?->update(['processing_status' => 'failed']);

        Log::critical(
            "CompressVideoJob: Permanently failed for media #{$this->mediaId}: {$exception->getMessage()}"
        );
    }
}


class CompressVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry the job up to 2 times (3 total attempts) before marking as failed.
     */
    public int $tries = 3;

    /**
     * Allow up to 90 minutes for large files.
     */
    public int $timeout = 5400;

    // ─── Resolution & bitrate maps ────────────────────────────────────────────

    private const RESOLUTIONS = [
        '1080p' => ['width' => 1920, 'height' => 1080, 'video_bitrate' => 4000, 'audio_bitrate' => 192],
        '720p'  => ['width' => 1280, 'height' => 720,  'video_bitrate' => 2500, 'audio_bitrate' => 128],
        '480p'  => ['width' => 854,  'height' => 480,  'video_bitrate' => 1000, 'audio_bitrate' => 96],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function __construct(
        private readonly int    $mediaId,
        private readonly string $sourcePath,   // absolute path on disk to the raw upload
        private readonly string $quality,      // '1080p' | '720p' | '480p'
        private readonly string $outputDir,    // absolute path for the compressed output directory
        private readonly string $dbOutputPath, // relative path to store in the DB after compression
    ) {}

    public function handle(): void
    {
        $media = ProjectMedia::find($this->mediaId);

        if (! $media) {
            Log::error("CompressVideoJob: ProjectMedia #{$this->mediaId} not found, aborting.");
            return;
        }

        $settings = self::RESOLUTIONS[$this->quality] ?? self::RESOLUTIONS['720p'];

        // ── Derive output filename ─────────────────────────────────────────
        $basename         = pathinfo($this->sourcePath, PATHINFO_FILENAME);
        $outputFilename   = "{$basename}-compressed.mp4";
        $absoluteOutput   = rtrim($this->outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $outputFilename;

        File::ensureDirectoryExists($this->outputDir, 0755, true);

        // ── Build the relative DB path for the compressed file ─────────────
        $dbDir             = rtrim($this->dbOutputPath, '/');
        $compressedDbPath  = "{$dbDir}/{$outputFilename}";

        try {
            Log::info("CompressVideoJob: Starting compression of media #{$this->mediaId} → {$this->quality}");

            // H.264 + AAC format
            $format = (new X264('aac', 'libx264'))
                ->setKiloBitrate($settings['video_bitrate'])
                ->setAudioKiloBitrate($settings['audio_bitrate']);

            // Scale to target resolution, preserving aspect ratio.
            // The `scale` filter uses -2 to keep dimensions divisible by 2 (H.264 requirement).
            $w = $settings['width'];
            $h = $settings['height'];

            FFMpeg::fromDisk('local_public')
                ->open($this->sourcePath)
                ->addFilter(function ($filters) use ($w, $h) {
                    $filters->custom("scale=iw*min({$w}/iw\\,{$h}/ih):-2");
                })
                ->export()
                ->toDisk('local_public')
                ->inFormat($format)
                ->save($absoluteOutput);

            Log::info("CompressVideoJob: Compression done → {$absoluteOutput}");

            // ── Swap paths and mark ready ──────────────────────────────────
            $media->update([
                'file_path'         => $compressedDbPath,
                'processing_status' => 'ready',
            ]);

            // ── Clean up the raw original upload ──────────────────────────
            if (File::exists($this->sourcePath)) {
                File::delete($this->sourcePath);
                Log::info("CompressVideoJob: Deleted original file → {$this->sourcePath}");
            }

        } catch (\Throwable $e) {
            Log::error("CompressVideoJob: Failed for media #{$this->mediaId}: {$e->getMessage()}");

            // Surface failure; keep original_file_path intact so admin can re-process
            $media->update(['processing_status' => 'failed']);

            throw $e; // Let the queue retry / record failure
        }
    }

    /**
     * The job has exhausted all retry attempts.
     */
    public function failed(\Throwable $exception): void
    {
        $media = ProjectMedia::find($this->mediaId);
        $media?->update(['processing_status' => 'failed']);

        Log::critical("CompressVideoJob: Permanently failed for media #{$this->mediaId}: {$exception->getMessage()}");
    }
}
