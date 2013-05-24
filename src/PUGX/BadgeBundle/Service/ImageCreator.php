<?php

namespace PUGX\BadgeBundle\Service;

use Symfony\Bridge\Monolog\Logger;
use PUGX\BadgeBundle\Event\PackageEvent;


class ImageCreator
{
    private $logger;
    protected $dispatcher;
    protected $imageNames = array('empty'=> 'empty.png', 'downloads' => 'download.png');
    protected $imagePath;
    protected $fontPath;
    protected $defaultFont;
    protected $defaultImage;

    //same lenght
    CONST ERROR_TEXT_GENERIC          = 'ERR 1 ';
    CONST ERROR_TEXT_NOT_A_NUMBER     = 'ERR 2 ';
    CONST ERROR_TEXT_CLIENT_EXCEPTION = 'ERR 3 ';

    public function __construct(Logger $logger, $fontPath, $imagePath , $defaultFont = null, $defaultImage = null)
    {
        $this->logger = $logger;
        $this->fontPath = $fontPath;
        $this->imagePath = $imagePath;

        if (!$defaultFont) {
            $defaultFont = 'DroidSans.ttf';
        }
        if (!$defaultImage) {
            $defaultImage = $this->imageNames['empty'];
        }
        $this->defaultFont = $defaultFont;
        $this->defaultImage = $defaultImage;
    }

    /**
     * Function that should return a human readable number in a maximum number of chars.
     *
     * @param int $number
     * @param int $maxChar
     *
     * @return string
     */
    public function transformNumberToReadableFormat($number, $maxChar = 6)
    {
        $defaultFormatter = '%.0f %s';
        $dimensions = array(
            1000000000000 => 'bb',
            1000000000 => 'mm',
            1000000 => 'm',
            1000 => 'k',
            1 => ' ',
        );

        $number = floatval($number);
        if ($number < 1) {
            return self::ERROR_TEXT_NOT_A_NUMBER;
        }
        foreach ($dimensions as $key => $suffix) {
            if ($number >= $key) {
                $number = $number / $key;
                // 2 is strlen(' ' . '.');  space and dot
                $floatPointNumber = $maxChar - strlen($suffix) - 2 - strlen(intval($number));
                $formatter = $defaultFormatter;
                $decimal_part = $number - floor($number);

                if ($decimal_part > 0 && $floatPointNumber > 0) {
                    $formatter = '%.' . $floatPointNumber . 'f %s';
                }

                $readable = sprintf($formatter, $number, $suffix);
                $readable = str_pad($readable, $maxChar, ' ', STR_PAD_LEFT);

                return $readable;
            }
        }

        $this->logger->warning(sprintf('impossible to transform to readable number[%s] with [%d] chars', $number, $maxChar));
        return self::ERROR_TEXT_GENERIC;
    }

    /**
     *
     * @param $text
     * @param $imagePath
     * @param $fontPath
     * @param $color
     * @param $withShadow
     *
     * @return resource
     */
    public function createImage($text, $value, $imagePath, $fontPath, $withShadow = true)
    {
        $image = imagecreatefrompng($imagePath);
        $white = imagecolorallocate($image, 255, 255, 250);
        $black = imagecolorallocate($image, 0, 0, 0);
        $font_path = $fontPath;

        //text
        if ($withShadow) {
            $imageArray = imagettftext($image, 8.5, 0, 4, 14, $black, $font_path, $text);
        }
        $imageArray = imagettftext($image, 8.5, 0, 3, 13, $white, $font_path, $text);

        // value
        if ($withShadow) {
            $imageArray = imagettftext($image, 8, 0, 67, 14.5, $black, $font_path, $value);
        }
        $imageArray = imagettftext($image, 8, 0, 66, 13.5, $white, $font_path, $value);

        return $image;
    }

    /**
     * Create the 'downloads' image with the standard Font and standard Image.
     *
     * @param string $value text to add to the image
     *
     * @return resource
     */
    public function createImageDownloads($value, $text = 'downloads')
    {
        $imagePath = $this->imagePath . DIRECTORY_SEPARATOR . $this->imageNames['empty'];
        $fontPath = $this->fontPath . DIRECTORY_SEPARATOR . $this->defaultFont;

        return $this->createImage($text, $value, $imagePath, $fontPath, true);
    }


}