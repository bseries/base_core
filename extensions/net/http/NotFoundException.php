<?php

namespace base_core\extensions\net\http;

class NotFoundException extends \RuntimeException {

	protected $code = 404;
}

?>