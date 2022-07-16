<?php

namespace App\Http\Controllers;

use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
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
                'video' => 'required|mimes:mp4,mov,ogg,qt,webm|max:20000',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $my_video = $request->file('video');
            $extension = $request->video->extension();

            #video rate
            $midBitrate = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(500);
            #path to save converted video
            $docPath = 'my_movie' . time() . '.' . $extension;

            #start conversion process
            $hey = FFMpeg::open($request->file('video'));

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
                ->inFormat($midBitrate)
            // ->resize(640, 480)
                ->save($docPath);

            return $docPath;

            //return response()->json(['success' => 'Video uploaded successfully', 'code' => $hey], 200);

        } catch (Throwable $th) {
            throw $th;
        }

    }

    public function startmanipulation(Request $request)
    {

    }
}
