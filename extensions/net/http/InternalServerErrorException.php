<?php

namespace base_core\extensions\net\http;

class InternalServerErrorException extends \RuntimeException {

	protected $code = 500;
}

?>