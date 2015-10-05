/*!
 * Router
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

define('router', ['jquery'], function($) {

  // Expects to have access to a global `App` object that must
  // have a `routes` property defined on it, containing all
  // route mappings.
  window.router = {
    match: function(name, params) {
      var dfr = new $.Deferred();
      var template = App.routes[name];

      $.each(params || {}, function(k, v) {
        template = template.replace('__' + k.toUpperCase().replace(' ', '_') + '__', v);
      });
      dfr.resolve(template);

      return dfr;
    }
  };

  return window.router;
});
