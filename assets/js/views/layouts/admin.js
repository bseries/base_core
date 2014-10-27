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

require(['jquery', 'list', 'nprogress', 'notify', 'qtip', 'domready!'], function($, List, Progress) {

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

  var $list = $('.use-list');
  if ($list.length) {
    var listValueNames = [];

    $list.find('thead .list-sort').each(function() {
      listValueNames.push($(this).data('sort'));
    });
    var list = new List($list.get(0), {
      // Always show everything.
      page: $list.find('.list > *').length,
      // Does not work.
      // indexAsync: true,
      searchClass: 'list-search',
      sortClass: 'list-sort',
      valueNames: listValueNames,
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

      $newNested.find('input,select').each(function() {
        $(this).attr('name', $(this).attr('name').replace(/\[new\]/, '[' + key + ']'));
      });
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
    require(['jquery', 'mediaAttachment'], function($, MediaAttachment) {
        attachDirect.each(function(k, el) {
          MediaAttachment.direct(el, {endpoints: App.media.endpoints});
        });
        attachJoined.each(function(k, el) {
          MediaAttachment.joined(el, {endpoints: App.media.endpoints});
        });
    });
  }

  //
  // Automaticlly bind editors.
  //
  var editorElements = $('.use-editor');

  if (editorElements.length) {
    require(['jquery', 'editor', 'editor-media', 'editor-page-break'],
      function($, Editor, EditorMedia, EditorPageBreak) {

        var externalPlugins = {
          media: (new EditorMedia()).init({endpoints: App.media.endpoints}),
          'page-break': new EditorPageBreak()
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
    require(['jquery', 'jqueryUi'],
      function($) {
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

  //
  // Highlight anchored row if hash is inside table.
  //
  var hash = window.location.hash.substring(1);
  if (hash) {
    var $row = $('[data-id="' + hash + '"]');

    if ($row.is('tr') && $row.length) {
      $row.addClass('highlight-anchored');

      require(['scrollTo'], function(ScrollTo) {
        ScrollTo.offsets(
          $row.offset().left,
          $row.offset().top - Math.round($(window).height() * 0.10),
          300
        );
      });
    }
  }

  //
  // Enlarge images when hovering over them in a table.
  //
  var $img = $('td.media img');
  if ($img.length) {
    $img.qtip({
      style: {
        widget: false,
        def: false
      },
      show: {
        effect: false
      },
      hide: {
        effect: false
      },
      effect: false,
      content: {
        text: function(ev, api) {
          var $el = $(this);

          require(['router', 'thingsLoaded'], function(Router, ThingsLoaded) {
            var checker = new ThingsLoaded.ImageChecker();

            var dfr = Router.match('media:view', {'id': $el.data('media-id')})
              .then(function(url) {
                return $.getJSON(url);
              })
              .then(function(data) {
                var url = data.data.file.versions.fix2admin.url;
                checker.addUrl(url);

                checker.run().always(function() {
                  api.set('content.text', $('<img />').attr('src', url));
                });
              });
          });
          return 'Loading…';
        }
      }
    });
  }

  if ($('[data-echo]').length) {
    require(['echo'], function(Echo) {
      Echo.init({
        offset: 100,
        throttle: 250,
        unload: false
      });
    });
  }
});
