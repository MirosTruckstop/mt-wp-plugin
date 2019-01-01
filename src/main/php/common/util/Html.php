<?php
namespace MT\WP\Plugin\Common\Util;

/**
 * Collection of helper functions for HTML.
 */
class MT_Util_Html
{

	/**
	 * Output "selected"
	 *
	 * @param string $first  The first of two values to compare
	 * @param string $second The second argument, which gets compared to the first
	 *
	 * @return void
	 */
	public static function selected($first, $second)
	{
		if ($first == $second) {
			return ' selected';
		} else {
			return '';
		}
	}
	
	/**
	 * Output information box.
	 *
	 * @param string      $typ  E.g. 'exception' or 'delete'
	 * @param string|null $text Textmeldung
	 *
	 * @return void
	 */
	public static function box($typ, $text = null)
	{
		if ($typ === 'exception') {
			$id = 'red';
			$text = 'Der folgende Fehler ist aufgetreten:<br><br>' . $text;
		} elseif ($typ === 'save') {
			$id = 'green';
			$text = 'Daten wurden erfolgreich gespeichert!';
		} elseif ($typ === 'delete') {
			$id = 'green';
			$text = 'Daten wurden erfolgreich gelöscht!';
		} elseif ($typ === 'notDelete') {
			$id = 'red';
			$text = 'Daten konnten nicht gelöscht werden!';
		}

		echo '<div class="box" id="' . $id . '">' . $text . '</div>';
	}
	
	//_______ Button _______

	/**
	 * Output button
	 *
	 * @param string $link Link
	 * @param string $text Text
	 * @param string $typ  Typ
	 *
	 * @return void
	 */
	public static function button($link, $text, $typ = 'button')
	{
		echo '<a class="button" href="' . $link . '">' . $text . '</a>';
	}
	
	public static function addButton($link)
	{
		return '<a href="' . $link . '" class="add-new-h2">Erstellen</a>';
	}

	/**
	 * Output submit button
	 *
	 * @return void
	 */
	public static function submitButton()
	{
		echo '<input type="submit" value="Änderung speichern" class="button button-primary">';
	}

	/**
	 * Output cancel button
	 *
	 * @param string $link Link
	 *
	 * @return void
	 */
	public static function cancelButton($link)
	{
		if (!empty($link)) {
			self::button($link, 'Abbrechen', 'button');
		} else {
			self::button('javascript:history.back()', 'Abbrechen', 'button');
		}
	}
	
	/**
	 * Output pagination
	 *
	 * Note: $additionalLink is a workaround to fix links in admin area.
	 *
	 * @param string|null $totalNumberOfItem Galleries ID
	 * @param string      $page              Page number
	 * @param string      $num               Number of pictures per page
	 * @param string      $sort              Sortation
	 * @param string      $baseUrl           String added before pagination link
	 *
	 * @return void
	 */
	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.MethodDoubleUnderscore
	public static function __outputPagination($totalNumberOfItem, $page, $num, $sort, $baseUrl = null)
	{
		$resultString = '<div id="seiten_leiste"><p>';
		
		$total_pages = ceil($totalNumberOfItem / $num);
		
		// Eine Seite zurueck
		if ($page > 1) {
			$resultString .= self::_outputPaginationLink($page - 1, $num, $sort, '« '.__('Zurück', MT_NAME), $baseUrl);
		} else {
			$resultString .= '<span class="style_grew">« '.__('Zurück', MT_NAME).'</span>';
		}
		$resultString .= '&nbsp;&nbsp;|&nbsp;&nbsp;<b>'.__('Seite', MT_NAME).'</b>';
	
		$points = true;

		// Die einzelnen Seiten
		for ($i = 1; $i <= $total_pages; $i++) {
			// Current page
			if ($i == $page) {
				$resultString .= '&nbsp;&nbsp;<b>' . $i . '</b>';
			} elseif (abs($i - $page) < 4 || $i == 1 || $i == $total_pages) { // Page less in range of 3, first or laste page
				$resultString .= '<span class="screen-small-hide">&nbsp;&nbsp;';
				$resultString .= self::_outputPaginationLink($i, $num, $sort, $i, $baseUrl);
				$resultString .= '</span>';
				$points = true;
			} elseif ($points) {
				$resultString .= '<span class="screen-small-hide">&nbsp;&nbsp;...';
				$points = false;
				$resultString .= '</span>';
			}
		}
	
		// Eine Seite vor
		$resultString .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
		if ($page == $total_pages) {
			$resultString .= '<span class="style_grew">'.__('Weiter', MT_NAME).' »</span>';
		} else {
			$resultString .= self::_outputPaginationLink($page + 1, $num, $sort, __('Weiter', MT_NAME) . ' »', $baseUrl);
		}
		$resultString .= '</p></div>';
		return $resultString;
	}

	/**
	 * Output pagination's link
	 *
	 * Note: $additionalLink is a workaround to fix links in admin area.
	 *
	 * @param string $page    Page number
	 * @param string $num     Number of photos per page
	 * @param string $sort    Photo sort
	 * @param string $text    Link text
	 * @param string $baseUrl String added before pagination link
	 *
	 * @return void
	 */
	private function _outputPaginationLink($page, $num, $sort, $text, $baseUrl)
	{
		return '<a href="'.$baseUrl.'page='.$page.'&num='.$num.'&sort='.$sort.'">'.$text.'</a>';
	}
}
