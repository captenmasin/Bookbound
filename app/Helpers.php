<?php

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

if (! function_exists('averageColourFromImageResource')) {
    function averageColourFromImageResource(GdImage $img): string
    {
        $width = imagesx($img);
        $height = imagesy($img);
        $rTotal = $gTotal = $bTotal = $total = 0;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($img, $x, $y);
                $rTotal += ($rgb >> 16) & 0xFF;
                $gTotal += ($rgb >> 8) & 0xFF;
                $bTotal += $rgb & 0xFF;
                $total++;
            }
        }

        $rAverage = round($rTotal / $total);
        $gAverage = round($gTotal / $total);
        $bAverage = round($bTotal / $total);

        return sprintf('#%02x%02x%02x', $rAverage, $gAverage, $bAverage);
    }
}

if (! function_exists('getDominantColour')) {
    function getDominantColour(string $imagePath): string
    {
        $imageType = exif_imagetype($imagePath);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($imagePath);
                break;
            default:
                throw new Exception('Unsupported image type.');
        }

        try {
            return averageColourFromImageResource($img);
        } finally {
            imagedestroy($img);
        }
    }
}

if (! function_exists('getDominantColourFromMedia')) {
    function getDominantColourFromMedia(Media $media): string
    {
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            throw new Exception('Image file not found.');
        }

        $img = imagecreatefromstring($disk->get($path));

        if ($img === false) {
            throw new Exception('Unsupported image type.');
        }

        try {
            return averageColourFromImageResource($img);
        } finally {
            imagedestroy($img);
        }
    }
}

if (! function_exists('hexToRgb')) {
    function hexToRgb($hex): string
    {
        sscanf($hex, '#%02x%02x%02x', $r, $g, $b);

        return "$r, $g, $b";
    }
}

if (! function_exists('rgbToHue')) {
    function rgbToHue($r, $g, $b)
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        if ($delta == 0) {
            return 0;
        }

        if ($max == $r) {
            $hue = 60 * fmod((($g - $b) / $delta), 6);
        } elseif ($max == $g) {
            $hue = 60 * ((($b - $r) / $delta) + 2);
        } else {
            $hue = 60 * ((($r - $g) / $delta) + 4);
        }

        return ($hue < 0) ? $hue + 360 : $hue;
    }
}
