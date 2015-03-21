<?php

class MT_Admin_DashboardWidget {

	/**
	 * Time to delete temp files
	 *
	 * NOTE: 86400s = 24h
	 *
	 * @var int
	 */
	private $_deleteTime = 86400;

	public function outputContent() {
		$this->_testPhotoPaths();
		$this->_deleteTempFiles();
	}

	/**
	 * Tests, if all photos exists
	 *
	 * @return void
	 */
	private function _testPhotoPaths() {
		$error = false;

		$query = (new MT_QueryBuilder('mt_wp_'))
			->from('photo', 'path')
			->join('gallery', TRUE, 'name');
		foreach ($query->getResult('ARRAY_A') as $row) {				
			$file = MT_Photo::$__photoPath . $row['path'];
						
			if( !file_exists( $file ) ) {
				$error = true;
				echo '
			<p class="style_red">Fehler! Das Bild <a href="' . $file . '" target="_blank">' . $file . '</a> in der Galerie "' . $row['name'] . '" wurde nicht gefunden!</p>';
			}
		}
		if( !$error) {
			echo '
			<p class="style_green">Alles OK! Alle Bilder wurden gefunden!</p>';
		}
	}

	/**
	 * Deletes all temp files older then 24 hours
	 *
	 * @return void
	 */
	private function _deleteTempFiles() {
		$unToDate = time() - $this->_deleteTime;

		// Request number of temp files
		$mangementTemp = new MT_ManagementTemp();
		$numTempFiles = $mangementTemp->get_count('id', "temp_date <= '" . $unToDate . "'");			
		
		if($numTempFiles > 0) {
			echo '
			<p class="style_green">Alles OK! Temporäre Dateien wurden gelöscht ('. $row->numTempFiles . ')';

			// Delete temp files
			$mangementTemp->deleteAll("temp_date <= '". $unToDate . "'");
		}
	}
}