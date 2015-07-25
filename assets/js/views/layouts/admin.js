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

require(['jquery', 'nprogress', 'notify', 'domready!'], function($, Progress) {

  $('.compound-users').each(function() {
    var $el = $(this);

    $el.find('[type=checkbox]').on('change', function() {
      $el.find('[name$=user_id]').toggleClass('hide');
    });
  });

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
  // Form help
  //
  $('form .help').each(function() {
    var $help = $(this);
    var $input = $help.prev().find('input,textarea,select');

    $input.on('focusin', function() {
      $help.fadeIn();
    });
    $input.on('focusout', function() {
      $help.fadeOut();
    });
    $help.hide();

  });

  //
  // Table sorting/filtering
  //
  var $richIndex = $('.use-rich-index');
  if ($richIndex.length) {
    require(['richIndex'], function(RichIndex) {
      new RichIndex($richIndex);
    });
  }

  //
  // Nested management
  //
  $('.use-nested').each(function() {
    var $nested = $(this);
    var $nestedAdd = $nested.find('.nested-add');
    $nestedAdd.hide();

    $nested.find('.button.add-nested').on('click', function(ev) {
      ev.preventDefault();
      var $newNested = $nestedAdd.clone().show();

      var key = parseInt(Math.random() * 10000, 10);

      $newNested.find('input,select,textarea').each(function() {
        $(this).attr('name', $(this).attr('name').replace(/\[new\]/, '[' + key + ']'));
      });

      // New nested items may hava media attachment fields.
      if ($newNested.find('.use-media-attachment-direct').length) {
        require(['mediaAttachment'], function(MediaAttachment) {
          MediaAttachment.direct($newNested.find('.use-media-attachment-direct'));
        });
      }

      $nested.find('tbody').append($newNested);
    });

    $nested.on('click', '.delete-nested',function(ev) {
      ev.preventDefault();

      var $existing = $(this).parents('.nested-item');
      var $del = $existing.find('[name*=_delete]');

      $existing.fadeOut(function() {
        if ($del.length) {
          $del.val(true);
        } else {
          $existing.remove();
        }
      });
     });
  });

  //
  // Dynamic Title
  //
  var $headTitle = $('head title');
  var $headingTitle = $('.rich-page-title .title');
  var $titleInput = $('form input.use-for-title');

  var originalValue = $headingTitle.data('empty');
  // var originalValue = $headingTitle.text();

  $titleInput.on('keyup', function(ev) {
    var $el = $(this);

    if ($.trim($el.val())) {
      $headingTitle.text($el.val());
      $headTitle.text($headTitle.text().replace(/^[\w\s]+\s\-/, $el.val() + ' -'));
    } else {
      $headingTitle.text(originalValue);
      $headTitle.text($headTitle.text().replace(/^[\w\s]+\s\-/, originalValue + ' -'));
    }
  });

  //
  // Automatically bind media attachment.
  //
  var attachDirect = $('.use-media-attachment-direct');
  var attachJoined = $('.use-media-attachment-joined');

  if (attachDirect.length || attachJoined.length) {
    require(['mediaAttachment'], function(MediaAttachment) {
        attachDirect.each(function(k, el) {
          MediaAttachment.direct(el);
        });
        attachJoined.each(function(k, el) {
          MediaAttachment.joined(el);
        });
    });
  }

  //
  // Automaticlly bind editors.
  //
  var editorElements = $('.use-editor');

  if (editorElements.length) {
    require(['editor', 'editor-media'],
      function(Editor, EditorMedia) {

        var externalPlugins = {
          media: (new EditorMedia()).init({endpoints: App.media.endpoints})
        };

        var pluginsByClasses = function(el) {
          var classes = $(el).attr('class').split(/\s+/);
          var plugins = [];

          $.each(classes, function(k, v) {
            if (v.indexOf('editor-') === -1) {
              return;
            }
            v = v.replace('editor-', '');

            if (v in externalPlugins) {
              plugins.push(externalPlugins[v]);
            } else {
              plugins.push(v);
            }
          });
          return plugins;
        };

        var editor = null;
        editorElements.each(function(k, el) {
          editor = new Editor();
          editor.init($(el).find('textarea'), pluginsByClasses(el));
        });
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

});
