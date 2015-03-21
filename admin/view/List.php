<?php
/**
 * Klasse um Uebersichtstabellen zu erstellen
 *
 * @category   MT
 * @package    Admin
 */
class MT_View_List extends MT_Admin_Table_Common
{
	
	/**
	 * Construct MT_Admin_Table_Manage object
	 *
	 * @param	string	$typ   	Typ (e.g.: photograper)
	 * @param	string	$class	CSS table class
	 * @return	void
	 */
	public function __construct($model, $cssClass = 'widefat' ) {
		parent::__construct($model, $cssClass);
	}
	
	public function outputContent() {
		echo '<div class="wrap">';
		echo '<h2>' . $this->model->getName() . ' ' . MT_Functions::addButton( '?page=mt-'.$this->model.'&type=edit') . '</h2>';
        if ( $_GET['action'] === 'delete' ) {
			$this->_delete( $_GET['id'] );
        }
		parent::_outputForm();
		echo '</div>';
	}

	/**
	 * Output table body (Form: tr, td)
	 *
	 * @return void
	 */
	protected function _outputTableBody() {
		$j = 0;
		foreach ($this->getResult() as $row) {
			$j++;
			echo '<tr ' . ($j % 2 == 1? 'class="alternate"' : '') . '>
								<td><input type="checkbox" name="checked" value="' . $row[0] .'"></td>
								<td><a href="?page=mt-' . $this->model . '&type=edit&id=' . $row[0] .'">' . $row[1] .'</a></td>';
			for( $i = 2; $i < sizeof( $row ); $i++ ) {
				echo '
								<td>' . $this->fields[$i]->getString($row[$i]) .'</td>
	';
			}
			echo '			</tr>
	';
		}
	}
	
        /**
         * Delete database entry.
         * 
         * @param int   $id Data id
         */
        private function _delete( $id ) {
            
            $check = FALSE;
            
            /**
             * TODO: Workaround
             */
            if($this->model == 'photo') {
                /**
                 * @see MT_Admin_Photo
                 */
                require_once ( __LIBRARY__ . 'MT/Admin/Photo.php' );
                
                MT_Admin_Photo::__deletePhoto($id);
                $check = TRUE;
            }
            // Photographer
            else if ( $this->model == 'photographer' ) {
                if ( !MT_Photographer::__hasPhotos($id) ) {
                    $check = TRUE;
                }
            }
            
            if ( $check ) {
                $this->_dbTable->delete( $this->_typ . '_id = ?', $id);
                MT_Functions::box( 'delete' );
            } else {
                MT_Functions::box( 'notDelete' );
            }
        }	
}
?>