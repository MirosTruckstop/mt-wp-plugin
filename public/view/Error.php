<?php

class MT_View_Error extends MT_View_Common {
	
	private $message;
	
	public function __construct($message) {
		$this->message = $message;
		
		parent::setTitle(__('Fehler', 'mt-wp-plugin'));
		parent::setDescription(__('Fehler', 'mt-wp-plugin'));
	}

	public function outputContent() {
		echo $this->message;
	}
}
