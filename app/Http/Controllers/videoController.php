<?php

namespace App\Http\Controllers;

use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Validator;

class videoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __invoke(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'video' => 'required|mimes:mp4|max:500000',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $file = $request->file('video');
            $fileSize = $request->file('video')->getSize();
            $fileSizeInMb = number_format($fileSize / 1048576, 2);

            $name = time() . Str::random(5) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->put($name, file_get_contents($file));

            $path = storage_path("app/public/" . $name);

            $saveTo = public_path() . "/"; #storage_path("app/public/converted_songs/");
            $hdh = $saveTo . "trim" . time() . "." . $file->getClientOriginalExtension();

            $phew = exec("ffmpeg -i $path -ss 00:00:00 -t 00:00:20 $hdh");

            die();

            $hey = FFMpeg::fromDisk('public')->open($name);

            #$durationInSeconds = $hey->getDurationInSeconds();

            $h = storage_path('app/public/' . $name);
            $ffprobe = FFProbe::create();
            $video = $ffprobe->streams($h)->videos()->first();
            $width = $video->get('width');
            $height = $video->get('height');
           $duration = $video->get('duration');
            $bitrate = $video->get('bit_rate');

            $hey->addWatermark(function (WatermarkFactory $watermark) {
                $watermark->open('logo.jpg')
                    ->right(25)
                    ->bottom(25)
                    ->width(100)
                    ->height(100)
                    ->greyscale();
            })->export()->onProgress(function ($percentage, $remaining, $rate) {
                echo "{$remaining} seconds left at rate: {$rate}";
            })
                ->toDisk('converted_songs')
                ->inFormat(new \FFMpeg\Format\Video\X264('aac'))
                ->resize(640, 480)
                ->save('converted' . time() . '.mp4');

        } catch (Throwable $th) {
            throw $th;
        }

    }

    public function startmanipulation(Request $request)
    {

        exec("ffmpeg -i input.mp4 -ss 00:05:20 -t 00:10:00 -c:v copy -c:a copy output1.mp4");

        $docPath = 'my_movie' . time() . '.' . $extension;

        $midBitrate = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(250);
        $hey = FFMpeg::fromDisk('public')->open('b.mp4');

        $durationInSeconds = $hey->getDurationInSeconds();

        return $durationInSeconds;

        #add water mark to image
        $hey->addWatermark(function (WatermarkFactory $watermark) {
            $watermark->open('logo.jpg')
                ->right(25)
                ->bottom(25)
                ->width(100)
                ->height(100)
                ->greyscale();
        })->export()->onProgress(function ($percentage, $remaining, $rate) {
            echo "{$remaining} seconds left at rate: {$rate}";
        })
            ->toDisk('converted_songs')
            ->inFormat(new \FFMpeg\Format\Video\X264('aac'))
            ->resize(640, 480)
            ->save('converted' . time() . '.mp4');
    }
}
