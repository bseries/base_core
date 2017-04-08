<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\config\bootstrap;

use Exception;
use lithium\security\validation\FormSignature;

if (strlen(PROJECT_SECRET_BASE) < 20) {
	throw new Exception('PROJECT_SECRET_BASE is less than 20 chars.');
}

FormSignature::config([
	'secret' => hash('sha512', PROJECT_SECRET_BASE)
]);

?>