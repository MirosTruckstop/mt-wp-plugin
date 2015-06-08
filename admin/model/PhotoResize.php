<?php

class MT_Admin_Model_PhotoResize {
	
	/**
	 * Maximal width of a thumbnail
	 */
	const MAX_WIDTH = 250;
	/**
	 * Maximal height of a thumbnail
	 */
	const MAX_HEIGHT = 150;
	/**
	 * Quality of a thumbnail
	 */
	const QUALITY = 90;

/*	public function resizeAllImages() {
		$photo = new MT_Photo();
		$i = 0;
		$min = 0;
		$range = 250;

		foreach ($photo->getAll() as $item) {
			if ($i >= $min && $i<$min+$range) {
				//echo $i;
				$this->resizeImage(MT_Photo::PHOTO_PATH.$item->path, MT_Photo::THUMBNAIL_PATH.$item->path);
			} else if($i >= $min+$range) {
				break;
			}
			$i++;
		}
	}*/

	/**
	 * Change the size of a image. The function is used to compare
	 * GD Library, ImageMagick and GraphicsMagick. 
	 *
	 * @param  string  $file     Image path
	 * @param  string  $name      Name of the new image
	 * @param  int     $maxWidth  Maximal width of the new image
	 * @param  int     $maxHeight Maximal height of the new image
	 * @param  int     $quality   From 1 to 100 (best)
	 * @return boolean $result    Success: true
	 */
	private function resizeImage($file, $name, $maxWidth = self::MAX_WIDTH, self::MAX_HEIGHT, $quality = self::QUALITY) {
		//echo $file.'<br>';
		$fileSize = getimagesize( $file );
		$width = $fileSize[0];
		$height = $fileSize[1];

		if( $width > $height ) {
			$factor = $maxWidth / $width;
		} else {
			$factor = $maxHeight / $height;
		}

		$newWidth = round( $width * $factor );
		$newHeight = round( $height * $factor );

		// Resize with GD Library (JPEG only!)	
		$oldImage = imagecreatefromjpeg( $file );
		// IF $oldImage is empty, $file is not a valid JPEG file
		if (!empty($oldImage)) {
			$newImage = imagecreatetruecolor( $newWidth, $newHeight );

			$result = imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagejpeg( $newImage, $name, $quality);

			return $result;
		} else {
			return FALSE;			
		}
	}
	
}