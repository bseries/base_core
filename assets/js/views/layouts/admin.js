/*!
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
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
  // Bridge between PHP flash messaging and JS notify.
  //
  var flashMessage = $('#messages').data('flash-message');
  var flashLevel = $('#messages').data('flash-level') || 'neutral';

  if (flashMessage) {
    require(['notify'], function() {
      $.notify(flashMessage, {level: flashLevel, timeout: 3000});
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
