<?php
namespace MT\WP\Plugin\Backend\View;

use MT\WP\Plugin\Api\MT_ManagementTemp;
use MT\WP\Plugin\Backend\Model\MT_Admin_Model_File;
use MT\WP\Plugin\Common\MT_QueryBuilder;

/**
 * Dashboard widget to display relevant information on the administration home
 * page.
 */
class MT_Admin_DashboardWidget
{

	/**
	 * Time to delete temp files
	 *
	 * NOTE: 86400s = 24h
	 *
	 * @var int
	 */
	private $_deleteTime = 86400;

	public function outputContent()
	{
		$this->_testPhotoPaths();
		$this->_deleteTempFiles();
	}

	/**
	 * Tests, if all photos exists
	 *
	 * @return void
	 */
	private function _testPhotoPaths()
	{
		$errorMessage = '';
		$errorCounter = 0;

		$query = (new MT_QueryBuilder())
			->from('photo', 'path')
			->join('gallery', true, 'name');
		foreach ($query->getResult() as $item) {
			$file = MT_Admin_Model_File::getPathFromDbPath($item->path);
			
			if (!file_exists($file)) {
				$errorCounter++;
				$errorMessage .= '<li>Fehler: <a href="'.$file.'" target="_blank">'.$file.'</a> aus der Galerie "'.$item->name.'" wurde nicht gefunden!</li>';
			}
			
			// Only display the first 10 errors
			if ($errorCounter >= 10) {
				break;
			}
		}
		
		// Output
		if ($errorCounter > 0) {
			echo '<ol>'.$errorMessage.'</ol>';
		} else {
			echo '<p>Alles OK! Alle Bilder wurden gefunden!</p>';
		}
	}

	/**
	 * Deletes all temp files older then 24 hours
	 *
	 * @return void
	 */
	private function _deleteTempFiles()
	{
		$unToDate = time() - $this->_deleteTime;

		// Request number of temp files
		$numTempFiles = MT_ManagementTemp::get_aggregate('COUNT', 'ip', "date <= '" . $unToDate . "'");
		
		if ($numTempFiles > 0) {
			echo '<p class="style_green">Alles OK! Temporäre Dateien wurden gelöscht ('.$numTempFiles.')';

			// Delete temp files
			MT_ManagementTemp::delete('date <= '.$unToDate);
		}
	}
}
