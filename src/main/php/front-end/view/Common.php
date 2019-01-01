<?php
namespace MT\WP\Plugin\Frontend\View;

/**
 * Common view
 */
abstract class MT_View_Common
{

	private $title;
	private $description;
	private $breadcrumb = array();
	
	abstract public function outputContent();
	
	public function setTitle($value)
	{
		$this->title = $value;
	}
	
	public function setDescription($value)
	{
		$this->description = $value;
	}
	
	public function setBreadcrumb(array $value)
	{
		$this->breadcrumb = $value;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function outputDescription()
	{
		echo $this->description;
	}

	public function getBreadcrumb()
	{
		return $this->breadcrumb;
	}
}
