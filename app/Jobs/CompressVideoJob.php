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
                        "scale=trunc(iw*min({$w}/iw\\,{$h}/ih)/2)*2:trunc(ih*min({$w}/iw\\,{$h}/ih)/2)*2,setsar=1"
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
