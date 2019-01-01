<?php
namespace MT\WP\Plugin\Backend\Model;

/**
 * Create a thumbnail photo.
 */
class MT_Admin_Model_ThumbnailCreator
{
	
	/**
	 * Maximal width of a thumbnail
	 */
	const MAX_WIDTH = 250;
	/**
	 * Maximal height of a thumbnail
	 */
	const MAX_HEIGHT = 175;
	/**
	 * Quality of a thumbnail
	 */
	const QUALITY = 90;

	/**
	 * Creates a thumbnail of the given photo $photoPath.
	 *
	 * @param string $photoPath     Path of the photo
	 * @param string $thumbnailPath Thumbnail path
	 *
	 * @return boolean True, if create was successful
	 */
	public static function create($photoPath, $thumbnailPath)
	{
		return self::resizeImage($photoPath, $thumbnailPath, self::MAX_WIDTH, self::MAX_HEIGHT, self::QUALITY);
	}

	/**
	 * Change the size of the given image $file and stores it as $name. It uses
	 * imagecopyresampled (GD Library).
	 *
	 * @param string $file      Image path
	 * @param string $name      Name of the new image
	 * @param int    $maxWidth  Maximal width of the new image
	 * @param int    $maxHeight Maximal height of the new image
	 * @param int    $quality   From 1 to 100 (best)
	 *
	 * @return boolean $result Success: true
	 */
	private static function resizeImage($file, $name, $maxWidth, $maxHeight, $quality)
	{
		//echo $file.'<br>';
		$fileSize = getimagesize($file);
		$width = $fileSize[0];
		$height = $fileSize[1];

		$factor = $maxHeight / $height;

		$newWidth = round($width * $factor);
		$newHeight = round($height * $factor);

		// Resize with GD Library (JPEG only!)
		$oldImage = imagecreatefromjpeg($file);
		// IF $oldImage is empty, $file is not a valid JPEG file
		if (!empty($oldImage)) {
			$newImage = imagecreatetruecolor($newWidth, $newHeight);

			$result = imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagejpeg($newImage, $name, $quality);

			return $result;
		} else {
			return false;
		}
	}
}
