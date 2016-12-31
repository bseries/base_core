#!/bin/bash
#
# Base Core
#
# Copyright (c) 2016 Atelier Disko - All rights reserved.
#
# Licensed under the AD General Software License v1.
#
# This software is proprietary and confidential. Redistribution
# not permitted. Unless required by applicable law or agreed to
# in writing, software distributed on an "AS IS" BASIS, WITHOUT-
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#
# You should have received a copy of the AD General Software
# License. If not, see http://atelierdisko.de/licenses.
#

curl https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css > assets/css/normalize.css
curl http://underscorejs.org/underscore.js > assets/js/underscore.js
curl https://code.jquery.com/jquery-3.1.1.js > assets/js/jquery.js
curl http://requirejs.org/docs/release/2.3.2/comments/require.js > assets/js/require.js
curl https://raw.githubusercontent.com/requirejs/domReady/latest/domReady.js > assets/js/require/domready.js

