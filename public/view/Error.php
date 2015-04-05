<?php

class MT_View_Error extends MT_View_Common {
	
	private $message;
	
	public function __construct($message) {
		$this->message = $message;
		
		parent::setTitle('Fehler');
		parent::setDescription('Fehler');
	}

	public function outputContent() {
		echo $this->message;
	}
}
