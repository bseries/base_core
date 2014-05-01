/*!
 * Bureau Core
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
    'compat': 'cms-core/js/require/compat',
    'domready': 'cms-core/js/require/domready',
    'text': 'cms-core/js/require/text',
    'async': 'cms-core/js/require/async',
    'goog': 'cms-core/js/require/goog',
    'propertyParser': 'cms-core/js/require/propertyParser',
    'jquery': 'cms-core/js/jquery',
    'jqueryUi': 'cms-core/js/jqueryUi',
    'underscore': 'cms-core/js/underscore',

    // Other
    'util': 'cms-core/js/util',
    'notify': 'cms-core/js/notify',
    'editor': 'cms-core/js/editor',
    'editor-media': 'cms-core/js/editor/media',
    'editor-page-break': 'cms-core/js/editor/page-break',
    'wysihtml5': 'cms-core/js/wysihtml5',
    'globalize': 'cms-core/js/globalize',
    'modal': 'cms-core/js/modal',
    'nprogress': 'cms-core/js/nprogress',
    'handlebars': 'cms-core/js/handlebars',
    'thingsLoaded': 'cms-core/js/thingsLoaded',
    'moment': 'cms-core/js/moment',
    'sprintf': 'cms-core/js/sprintf',
    'caolan/async': 'cms-core/js/async',
    'list': 'cms-core/js/list',
    'listPagination': 'cms-core/js/listPagination',

    // Compat
    'versioncompare': 'cms-core/js/compat/versioncompare',
    'compatManager': 'cms-core/js/compatManager',
    'modernizr': 'cms-core/js/compat/modernizr',
    'cssparser': 'cms-core/js/cssparser',
    'cssFilters': 'cms-core/js/compat/cssFilters',
    'balanceText': 'cms-core/js/compat/balanceText',
    'inputDate': 'cms-core/js/compat/inputDate',
    'sendAsBinary': 'cms-core/js/compat/sendAsBinary',
    'animationFrame': 'cms-core/js/compat/animationFrame',
    'styleFix': 'cms-core/js/compat/styleFix',
    'vunits': 'cms-core/js/compat/vunits',
    'browserSwitch': 'cms-core/js/browserSwitch'
  },
  shim: {
    // Basics
    'underscore': {
      exports: '_'
    },
    'jquery': {
      exports: '$'
    },
    'jqueryUi': {
      deps: ['jquery'],
      exports: '$'
    },

    // App

    // Other
    'globalize': {
      deps: ['jquery'],
      exports: 'Globalize'
    },
    'globalize.en.messages': {
      deps: ['globalize']
    },
    'globalize.de': {
      deps: ['globalize']
    },
    'globalize.de.messages': {
      deps: ['globalize']
    },
    'notify': {
      deps: ['jquery', 'modernizr']
    },
    'wysihtml5': {
      exports: 'wysihtml5'
    },
    'handlebars': {
      exports: 'Handlebars'
    },
    'thingsLoaded': {
      deps: ['jquery'],
      exports: 'ThingsLoaded'
    },
    'sprintf': {
      exports: 'window.sprintf'
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
    'versioncompare': {
      exports: 'versionCompare'
    },
    'inputDate': {
      deps: ['jquery', 'domready!'],
      exports: 'inputDate'
    },
    'cssparser': {
      exports: 'CSSParser'
    },
    'cssFilters': {
      deps: ['cssparser', 'domready!']
    },
    'balanceText': {
      deps: ['jquery'],
      exports: 'jQuery.fn.balanceText'
    },
    'styleFix': {
      exports: 'styleFix'
    },
    'vunits': {
      deps: ['styleFix']
    }
  }
});
require(['jquery', 'notify', 'domready!'],
function($) {
  // Bridge between PHP flash messaging and JS notify.
  var flashMessage = $('#messages').data('flash-message');
  var flashLevel = $('#messages').data('flash-level') || 'neutral';

  if (flashMessage) {
    $.notify(flashMessage, flashLevel);
  }
});

require(['jquery', 'nprogress', 'domready!'],
function($, Progress) {
  Progress.configure({
    showSpinner: false
  });
  $(document).on('modal:isLoading', function() { Progress.start(); });
  $(document).on('modal:newContent', function() { Progress.done(); });
  $(document).on('modal:isReady', function() {
    Progress.done();

    setTimeout(function() {
      Progress.remove();
    }, 500);
  });
  $(document).on('transfer:start', function() { Progress.start(); });
//  $(document).on('transfer:progress', function(ev, data) { Progress.set(data); });
  $(document).on('transfer:done', function(data) { Progress.done(); });
});

require(['jquery', 'domready!'], function($) {
  var hasTouch = (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch);
  if (hasTouch) {
    $('body').addClass('touch');
  } else {
    $('body').addClass('no-touch');
  }
});
