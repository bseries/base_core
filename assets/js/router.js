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
 * License. If not, see https://atelierdisko.de/licenses.
 */

define('router', ['jquery'], function($) {

  // Expects to have access to a global `App` object that must
  // have a `routes` property defined on it, containing all
  // route mappings.
  if (window.console !== undefined) {
    if (window.App === undefined) {
        console.error('Global App object not defined.');
    } else if (window.App.routes === undefined) {
        console.error('Global App object hast no routes key.');
    }
  }

  // Router only has one method (`match`) and is exported globally. This method
  // currently returns a promise for BC and FC. We may later resolve routes
  // via an API endpoint.
  window.router = {
    match: function(name, params) {
      var dfr = new $.Deferred();
      var template = App.routes[name];

      $.each(params || {}, function(k, v) {
        template = template.replace(
          '__' + _underscore(k).toUpperCase() + '__',
          v
        );
      });
      dfr.resolve(template);

      return dfr;
    }
  };

  // Helper function to turn camlized strings into
  // underscored i.e. (foreignKey -> foreign_Key).
  function _underscore(camelized) {
    var result = '' + camelized;

    result = result.replace(/([A-Z\d]+)([A-Z][a-z])/g, '$1_$2');
    result = result.replace(/([a-z\d])([A-Z])/g, '$1_$2');
    result = result.replace(/-/g, '_');

    return result;
  }

  return window.router;
});
