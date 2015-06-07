<?php

class MT_View_Error extends MT_View_Common {
	
	private $message;
	
	public function __construct($message) {
		$this->message = $message;
		
		parent::setTitle(__('Fehler', MT_NAME));
		parent::setDescription(__('Fehler', MT_NAME));
	}

	public function outputContent() {
		echo $this->message;
	}
}
