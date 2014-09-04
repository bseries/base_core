/*!
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

define('router', ['jquery'], function($) {

  if (window.router !== undefined) {
    return window.router; // static
  }

  function Router() {
    var _this = this;

    this.data = {};

    this.initialized = null;

    this.init = function() {
      _this.initialized = new $.Deferred();

      $.getJSON(App.api.discover).done(function(data) {
        $.each(data, function(k, v) {
          _this.connect(k, v);
        });
        _this.initialized.resolve();
      });

      return _this;
    };

    this.connect = function(name, template) {
      _this.data[name] = template;
    };

    this.match = function(name, params) {
      return _this.initialized.then(function() {
        var template = _this.data[name];
        $.each(params || {}, function(k, v) {
          template = template.replace('__' + k.toUpperCase() + '__', v);
        });

        return template;
      });
    };
  }

  window.router = new Router();
  window.router.init();
  return window.router;
});
