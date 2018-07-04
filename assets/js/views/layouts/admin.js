/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

require(['jquery', 'nprogress', 'moment', 'domready!'], function($, Progress, Moment) {
  'use strict';

  //
  // Progress setup
  //
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

  //
  // Closing bridge between application and JavaScript notifications.
  //
  if (App.flash) {
    require(['notify'], function() {
      $.notify(App.flash.message, {
        level: App.flash.attrs.level || 'neutral',
        timeout: 3000
      });
    });
  }

  //
  // Rich forms
  //
  var $richForm = $('#content article');
  if ($richForm.find('form').length) {
    require(['richForm'], function(RichForm) {
      new RichForm($richForm);
    });
  }

  //
  // Rich Table sorting/filtering
  //
  var $richIndex = $('.use-rich-index');
  if ($richIndex.length) {
    require(['richIndex'], function(RichIndex) {
      new RichIndex($richIndex);
    });
  }

  //
  // Automatically bind sortables.
  //
  var $sortableElement = $('.use-manual-sorting');
  if ($sortableElement.length) {
    require(['sortableIndex'], function(SortableIndex) {
      new SortableIndex($sortableElement);
    });
  }

  $('button[type=submit]').on('click', function(ev) {
    $(this).addClass('loading');
  });

  //
  // Session Expiry Display
  //
  Moment.locale($('html').attr('lang'));

  var $expiry = $('.logout__in');
  var expiry = Moment().add(parseInt($expiry.data('seconds'), 10), 'seconds');

  $expiry.text(expiry.from());

  setInterval(function() {
    var now = Moment();

    // Warn 2 minutes before expiration; error after expiration.
    if (expiry.isBefore(now)) {
      $expiry.parent().addClass('error').removeClass('plain');
    } else if (expiry.diff(now) < (60 * 2 * 100)) {
      $expiry.parent().addClass('warning').removeClass('plain');
    }

    $expiry.text(expiry.from());
  }, 1000); // 1s
});
