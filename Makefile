#
# Base Core
#
# Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
#
# This software is proprietary and confidential. Redistributions
# not permitted. Unless required by applicable law or agreed to
# in writing, software distributed on an "AS IS" BASIS, WITHOUT
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#
JSDIR = $(CURDIR)/assets/js 

#
# Updates all JavaScript used by the admin interface.
#
.PHONY: js
js: notify editor jquery2 listjs handlebars nprogress requirejs compat-minimal modal

router:
	cp -r ~/Code/ad/router/src/* $(JSDIR)/

modal:
	cp -r ~/Code/ad/modal/src/* $(JSDIR)/

editor: 
	cp -r ~/Code/ad/editor/src/* $(JSDIR)/

notify:
	curl https://raw.githubusercontent.com/davidpersson/notify/master/notify.js > $(JSDIR)/notify.js

nprogress:
	curl https://raw.githubusercontent.com/rstacruz/nprogress/master/nprogress.js > $(JSDIR)/nprogress.js

handlebars:
	curl http://builds.handlebarsjs.com.s3.amazonaws.com/handlebars-v1.3.0.js > $(JSDIR)/handlebars.js	

listjs:
	curl https://raw.githubusercontent.com/javve/list.js/master/dist/list.js > $(JSDIR)/list.js
	curl https://raw.githubusercontent.com/javve/list.pagination.js/master/dist/list.pagination.js > $(JSDIR)/listPagination.js

requirejs:
	curl https://raw.githubusercontent.com/jrburke/requirejs/master/require.js > $(JSDIR)/require.js
	git clone https://github.com/millermedeiros/requirejs-plugins.git /tmp/rjsplugins
	cp -v /tmp/rjsplugins/src/async.js $(JSDIR)/require/
	cp -v /tmp/rjsplugins/src/goog.js $(JSDIR)/require/
	cp -v /tmp/rjsplugins/src/json.js $(JSDIR)/require/
	cp -v /tmp/rjsplugins/src/propertyParser.js $(JSDIR)/require/
	curl https://raw.githubusercontent.com/requirejs/domReady/master/domReady.js > $(JSDIR)/require/domReady.js
	curl https://raw.githubusercontent.com/requirejs/text/master/text.js > $(JSDIR)/require/text.js
	rm -r /tmp/rjsplugins

jquery2:
	curl http://code.jquery.com/jquery-2.1.1.js > $(JSDIR)/jquery.js

compat-minimal:
	git clone https://github.com/atelierdisko/compat.git /tmp/compat
	cp -vr /tmp/compat/src/js/compat/modnerizr.js $(JSDIR)/compat/
	rm -r /tmp/compat
	
