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
 * License. If not, see http://atelierdisko.de/licenses.
 */

require(['jquery', 'nprogress', 'notify', 'domready!'], function($, Progress) {

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
    $.notify(flashMessage, {level: flashLevel});
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
  var sortableElement = $('.use-manual-sorting');
  if (sortableElement.length) {
    require(['jqueryUi'],
      function() {
        sortableElement.sortable({
          placeholder: 'sortable-placeholder',
          items: '> tr',
          update: function(ev, ui) {
            var ids = [];
            sortableElement.find('tr').each(function(k, v) {
              ids.push($(v).data('id'));
            });
            $.ajax({
              type: 'POST',
              // Assumes we are on an index page and can relatively get to the endpoint.
              url: window.location.pathname + '/order',
              data: {'ids': ids},
            }).done(function() {
              $.notify('Sortierung gespeichert.', 'success');
            }).fail(function() {
              $.notify('Speichern der Sortierung fehlgeschlagen.', 'error');
              $.notify('Stellen Sie sicher, dass AdBlock für diese Domain deaktiviert ist.');
            });
          }
        });
    });
  }

  $('button[type=submit]').on('click', function(ev) {
    $(this).addClass('loading');
  });

  var $expiry = $('.session-expiry__seconds');
  setInterval(function() {
    var value = parseInt($expiry.text(), 10);

    $expiry.text(value - 1);
  }, 1000);

});
