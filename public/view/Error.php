<?php

class MT_View_Error implements MT_View_ICommon {
	
	private $message;
	
	public function __construct($message) {
		$this->message = $message;
	}

	public function outputTitle() {
		echo 'Fehler';
	}
	
	public function outputDescription() {
		echo 'Fehler';
	}
	
	public function outputContent() {
		echo $this->message;
	}
}
