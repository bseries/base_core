/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

requirejs.config({
  config: {
    text: {
      // Allow cross-domain requests, server features CORS.
      useXhr: function() { return true; }
    }
  },
  baseUrl: App.assets.base,
  waitSeconds: 15,
  paths: {
    // Basics
    'jquery': 'base-core/js/jquery',
    'jqueryUi': 'base-core/js/jqueryUi',
    'router': 'base-core/js/router',
    'underscore': 'base-core/js/underscore',

    'domready': 'base-core/js/require/domready',
    'text': 'base-core/js/require/text',
    'async': 'base-core/js/require/async',
    'propertyParser': 'base-core/js/require/propertyParser',

    // Other
    'util': 'base-core/js/util',
    'notify': 'base-core/js/notify',
    'editor': 'base-core/js/editor',
    'editor-media': 'base-core/js/editor/media',
    'editor-code': 'base-core/js/editor/code',
    'wysihtml5': 'base-core/js/wysihtml5',
    'modal': 'base-core/js/modal',
    'nprogress': 'base-core/js/nprogress',
    'handlebars': 'base-core/js/handlebars',
    'widgets': 'base-core/js/widgets',
    'moment': 'base-core/js/moment',
    'qtip': 'base-core/js/qtip',
    'thingsLoaded': 'base-core/js/thingsLoaded',
    'richIndex': 'base-core/js/richIndex',
    'richForm': 'base-core/js/richForm',
    'sortableIndex': 'base-core/js/sortableIndex',
    'waypoints': 'base-core/js/waypoints',
    'minigrid': 'base-core/js/minigrid',
    'translator': 'base-core/js/translator',

    // Compat
    'modernizr': 'base-core/js/compat/modernizr',
  },
  shim: {
    'jquery': {
      exports: '$'
    },
    'jqueryUi': {
      deps: ['jquery'],
      exports: '$'
    },

    // App (here Admin)
    'notify': {
      deps: ['jquery', 'modernizr']
    },
    'wysihtml5': {
      exports: 'wysihtml5'
    },
    'handlebars': {
      exports: 'Handlebars'
    },
    'qtip': {
      deps: ['jquery']
    },
    'thingsLoaded': {
      deps: ['jquery'],
      exports: 'ThingsLoaded'
    },
    'waypoints': {
      deps: ['jquery'],
      exports: 'Waypoint'
    },

    // Compat
    'modernizr': {
      exports: 'Modernizr',
      deps: ['domready!']
    },
  }
});


