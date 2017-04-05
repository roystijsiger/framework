<?php
class HttpResult{
	public $Statuscode;
	public $AdditionalHeaders;
	public $Response;

	function __construct($statuscode, $headers, $body = ''){
		$this->Statuscode = $statuscode;
		$this->AdditionalHeaders = $headers;
		$this->Response = $body;
	}
}