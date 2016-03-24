/*!
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
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
    'domready': 'base-core/js/require/domready',
    'text': 'base-core/js/require/text',
    'async': 'base-core/js/require/async',
    'propertyParser': 'base-core/js/require/propertyParser',
    'jquery': 'base-core/js/jquery',
    'jqueryUi': 'base-core/js/jqueryUi',
    'router': 'base-core/js/router',
    'underscore': 'base-core/js/underscore',

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
    'scrollTo': 'base-core/js/scrollTo',
    'qtip': 'base-core/js/qtip',
    'thingsLoaded': 'base-core/js/thingsLoaded',
    'richIndex': 'base-core/js/richIndex',
    'richForm': 'base-core/js/richForm',
    'waypoints': 'base-core/js/waypoints',
    'minigrid': 'base-core/js/minigrid',

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
    'underscore': {
      exports: '_'
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


