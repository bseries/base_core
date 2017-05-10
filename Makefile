#
# Base Core
#
# Copyright (c) 2016 Atelier Disko - All rights reserved.
#
# This software is proprietary and confidential. Redistributions
# not permitted. Unless required by applicable law or agreed to
# in writing, software distributed on an "AS IS" BASIS, WITHOUT
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#

ASSETS_PATH = assets

.PHONY: update-assets
update-assets:
	curl https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css > $(ASSETS_PATH)/css/normalize.css
	curl http://underscorejs.org/underscore.js > $(ASSETS_PATH)/js/underscore.js
	curl https://code.jquery.com/jquery-3.1.1.js > $(ASSETS_PATH)/js/jquery.js
	curl http://requirejs.org/docs/release/2.3.2/comments/require.js > $(ASSETS_PATH)/js/require.js
	curl https://raw.githubusercontent.com/requirejs/domReady/latest/domReady.js > $(ASSETS_PATH)/js/require/domready.js
	curl https://raw.githubusercontent.com/imakewebthings/waypoints/master/lib/noframework.waypoints.js > $(ASSETS_PATH)/js/waypoints.js

