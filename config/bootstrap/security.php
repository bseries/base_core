<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\config\bootstrap;

use lithium\security\validation\FormSignature;

FormSignature::config([
	'secret' => hash('sha512', PROJECT_SECRET_BASE)
]);

?>