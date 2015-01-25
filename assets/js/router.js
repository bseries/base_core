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

  // Expects to have access to a global `App` object that must
  // have a `routes` property defined on it, containing all
  // route mappings.
  function Router() {
    var _this = this;

    this.match = function(name, params) {
      var dfr = new $.Deferred();
      var template = App.routes[name];

      $.each(params || {}, function(k, v) {
        template = template.replace('__' + k.toUpperCase() + '__', v);
      });
      dfr.resolve(template);

      return dfr;
    };
  }

  window.router = Router;
  return window.router;
});
