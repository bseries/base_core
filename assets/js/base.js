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

requirejs.config({
  config: {
    text: {
      // Allow cross-domain requests, server features CORS.
      useXhr: function() { return true; }
    }
  },
  baseUrl: App.assets.base + '/v:__PROJECT_VERSION_BUILD__',
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

    // Other
    'util': 'base-core/js/util',
    'notify': 'base-core/js/notify',
    'editor': 'base-core/js/editor',
    'editor-media': 'base-core/js/editor/media',
    'editor-page-break': 'base-core/js/editor/page-break',
    'wysihtml5': 'base-core/js/wysihtml5',
    'modal': 'base-core/js/modal',
    'nprogress': 'base-core/js/nprogress',
    'handlebars': 'base-core/js/handlebars',
    'list': 'base-core/js/list',
    'widgets': 'base-core/js/widgets',
    'moment': 'base-core/js/moment',

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
    'list': {
      exports: 'window.List'
    },
    'listPagination': {
      exports: 'window.ListPagination',
      deps: ['list']
    },

    // Compat
    'modernizr': {
      exports: 'Modernizr',
      deps: ['domready!']
    },
  }
});


