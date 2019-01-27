<?php
namespace Czim\FileHandling\Support\Image;

use ErrorException;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use SplFileInfo;

/**
 * Class OrientationFixer
 *
 * Uses Exif data to fix the orientation of an image, if it is rotated or flipped.
 */
class OrientationFixer
{
    const ORIENTATION_TOPLEFT     = 1;
    const ORIENTATION_TOPRIGHT    = 2;
    const ORIENTATION_BOTTOMRIGHT = 3;
    const ORIENTATION_BOTTOMLEFT  = 4;
    const ORIENTATION_LEFTTOP     = 5;
    const ORIENTATION_RIGHTTOP    = 6;
    const ORIENTATION_RIGHTBOTTOM = 7;
    const ORIENTATION_LEFTBOTTOM  = 8;

    /**
     * Whether to silently ignore exceptions.
     *
     * @var bool
     */
    protected $quiet = true;

    /**
     * @var ImagineInterface
     */
    protected $imagine;


    /**
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }


    /**
     * @return $this
     */
    public function enableQuietMode()
    {
        $this->quiet = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableQuietMode()
    {
        $this->quiet = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isQuiet()
    {
        return $this->quiet;
    }

    /**
     * Fixes the orientation in a local file.
     *
     * This overwrites the current file.
     *
     * @param SplFileInfo $file
     * @return bool
     * @throws ErrorException
     */
    public function fixFile(SplFileInfo $file)
    {
        $filePath = $file->getRealPath();

        $image = $this->imagine->open($file->getRealPath());

        $image = $this->fixImage($filePath, $image);
        $image->save();

        return true;
    }

    /**
     * Re-orient an image using its embedded Exif profile orientation.
     *
     * 1. Attempt to read the embedded exif data inside the image to determine it's orientation.
     *    if there is no exif data (i.e an exeption is thrown when trying to read it) then we'll
     *    just return the image as is.
     * 2. If there is exif data, we'll rotate and flip the image accordingly to re-orient it.
     * 3. Finally, we'll strip the exif data from the image so that there can be no
     *    attempt to 'correct' it again.
     *
     * @param string         $path
     * @param ImageInterface $image
     * @return ImageInterface $image
     * @throws ErrorException
     */
    public function fixImage($path, ImageInterface $image)
    {
        // @codeCoverageIgnoreStart
        if ( ! function_exists('exif_read_data')) {
            return $image;
        }
        // @codeCoverageIgnoreEnd

        try {
            $exif = exif_read_data($path);
            // @codeCoverageIgnoreStart
        } catch (ErrorException $e) {
            if ($this->quiet) {
                return $image;
            }
            throw $e;
            // @codeCoverageIgnoreEnd
        }

        if ( ! isset($exif['Orientation']) || $exif['Orientation'] == static::ORIENTATION_TOPLEFT) {
            return $image;
        }

        switch ($exif['Orientation']) {

            case static::ORIENTATION_TOPRIGHT:
                $image->flipHorizontally();
                break;

            case static::ORIENTATION_BOTTOMRIGHT:
                $image->rotate(180);
                break;

            case static::ORIENTATION_BOTTOMLEFT:
                $image->flipVertically();
                break;

            case static::ORIENTATION_LEFTTOP:
                $image->flipVertically()->rotate(90);
                break;

            case static::ORIENTATION_RIGHTTOP:
                $image->rotate(90);
                break;

            case static::ORIENTATION_RIGHTBOTTOM:
                $image->flipHorizontally()->rotate(90);
                break;

            case static::ORIENTATION_LEFTBOTTOM:
                $image->rotate(-90);
                break;
        }

        return $image->strip();
    }

}
