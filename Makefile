# Copyright 2016 Atelier Disko. All rights reserved.
#
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

ASSETS_PATH = assets

.PHONY: update-assets
update-assets:
	curl https://raw.githubusercontent.com/necolas/normalize.css/master/normalize.css > $(ASSETS_PATH)/css/normalize.css
	curl http://underscorejs.org/underscore.js > $(ASSETS_PATH)/js/underscore.js
	curl https://code.jquery.com/jquery-3.1.1.js > $(ASSETS_PATH)/js/jquery.js
	curl http://requirejs.org/docs/release/2.3.2/comments/require.js > $(ASSETS_PATH)/js/require.js
	curl https://raw.githubusercontent.com/requirejs/domReady/latest/domReady.js > $(ASSETS_PATH)/js/require/domready.js
	curl https://raw.githubusercontent.com/imakewebthings/waypoints/master/lib/noframework.waypoints.js > $(ASSETS_PATH)/js/waypoints.js
	curl -L https://raw.githubusercontent.com/zloirock/core-js/master/client/shim.js > $(ASSETS_PATH)/js/compat/core.js

