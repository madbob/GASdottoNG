<?php

namespace App\Exceptions;

class AuthException extends \Exception {
	
	private $status;
	
	public function __construct(int $status)
	{
		$this->status = $status;
	}

	public function status() {
		return $this->status;
	}
	
}
