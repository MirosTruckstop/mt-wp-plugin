<?php
/**
 * Common view
 * 
 * @package public
 * @subpackage view
 */
abstract class MT_View_Common {

	private $title;
	private $description;
	private $breadcrumb;
	
	public abstract function outputContent();
	
	public function setTitle($value) {
		$this->title = $value;
	}
	
    public function setDescription($value) {
		$this->description = $value;
	}
	
	public function setBreadcrumb(array $value) {
		$this->breadcrumb = $value;
	}
	
	public function outputTitle() {
		echo $this->title;
	}
	
    public function outputDescription() {
		echo $this->description;
	}

	public function outputBreadcrumb() {
		if ($this->breadcrumb) {
			echo '<div id="link_leiste" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';

			foreach ($this->breadcrumb as $link => $label) {
				echo '<a href="'.$link.'" itemprop="url"><span itemprop="title">'.$label.'</span></a>';
				if (!empty($link)) {
					echo '&nbsp;>&nbsp;';
				}
			}
			echo '</div>';
		}
	}
}