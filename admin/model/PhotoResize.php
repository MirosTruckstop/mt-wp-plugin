<?php

class MT_Admin_Model_PhotoResize {

/*	public function resizeAllImages($maxWidth, $maxHeight, $quality) {
		$photo = new MT_Photo();
		$i = 0;
		foreach ($photo->getAll() as $item) {
			if ($i >= 300 && $i<400) {
				echo $this->resizeImage(MT_Photo::$__photoPath.$item->path, MT_Admin_Model_PhotoResize::$thumbnailPath.$item->path, $maxWidth, $maxHeight, $quality);
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
	 * @param  string  $tpy       "gm", "im" or "gm"
	 * @param  int     $quality   From 1 to 100 (best)
	 * @return boolean $result    Success: true
	 */
	private function resizeImage($file, $name, $maxWidth, $maxHeight, $quality = 75) {
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
		$newImage = imagecreatetruecolor( $newWidth, $newHeight );

		$result = imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		imagejpeg( $newImage, $name, $quality);

		return $result;
	}
	
}