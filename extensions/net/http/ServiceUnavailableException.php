<?php

namespace base_core\extensions\net\http;

class ServiceUnavailableException extends \RuntimeException {

	protected $code = 503;
}

?>