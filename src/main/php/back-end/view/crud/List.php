<?php
namespace MT\WP\Plugin\Backend\View\Crud;

use MT\WP\Plugin\Common\Util\MT_Util_Html;

/**
 * Admin list view, i.e. list a entries of an entity.
 */
class MT_View_List extends MT_Admin_View_Common
{
	
	/**
	 * Construct MT_Admin_Table_Manage object
	 *
	 * @param object $model Model
	 *
	 * @return void
	 */
	public function __construct($model)
	{
		parent::__construct($model);
		parent::setTitle($this->model->getName().' '.MT_Util_Html::addButton('?page=mt-'.$this->model->name().'&type=edit'));
	}
	
	protected function outputHeadMessages()
	{
	}

	protected function _outputTableNavBottom()
	{
	}

	/**
	 * Output table body (Form: tr, td)
	 *
	 * @return void
	 */
	protected function _outputTableBody()
	{
		$j = 0;
		foreach ($this->getResult() as $item) {
			$j++;
			echo '<tr ' . ($j % 2 == 1? 'class="alternate"' : '') . '>
					<td><input type="checkbox" name="checked" value="' . $item['id'] .'"></td>';
			for ($i = 1; $i < count($this->fields); $i++) {
				$field = $this->fields[$i];
				if ($i == 1) {
					echo '<td><a href="?page=mt-'.$this->model->name().'&type=edit&id=' . $item['id'] .'">' . $item[$field->name] .'</a></td>';
				} else {
					echo '<td>'.$field->getString($item[$field->name]).'</td>';
				}
			}
			echo '</tr>';
		}
	}
}
