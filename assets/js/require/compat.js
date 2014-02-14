/*!
 * Compat RequireJS Plugin
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */
define('compat', function(module) {
  var normalize = function(name, normalize) {
    return normalize(name);
  };

  var load = function(name, req, onload) {
    req(['compatManager'], function(Manager) {
      Manager.run([
        name
      ]).done(onload);
    });
  };

  return {
    load: load,
    normalize: normalize
  };
});