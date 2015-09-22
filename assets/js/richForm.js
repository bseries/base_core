/*!
 * Index Table
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
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

define(['jquery'],
function($) {

    return function RichForm($element) {
      var _this = this;

      this.$element = $element;

      this._initNested = function() {
        _this.$element.find('.use-nested').each(function() {
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
      };

      //
      // Dynamic Title
      //
      this._initDynamicTitle = function() {
        var $headTitle = $('head title');
        var $headingTitle = $('.rich-page-title .title');
        var $titleInput = _this.$element.find('form input.use-for-title');

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
      };

      //
      // Automatically bind media attachment.
      //
      this._initMediaAttachment = function() {
        var attachDirect = _this.$element.find('.use-media-attachment-direct');
        var attachJoined = _this.$element.find('.use-media-attachment-joined');

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
      };

      //
      // Automaticlly bind editors.
      //
      this._initEditor = function() {
        var editorElements = _this.$element.find('.use-editor');

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
      };

      this._initFollowActions = function() {
        var $el = _this.$element.find('.bottom-actions');

        $el.on('click', function() {
          $el.toggleClass('revealed');
        });

        require(['waypoints'], function(Waypoint) {
          new Waypoint({
            element: $('#content')[0],
            handler: function(dir) {
              if (dir === 'down') {
                  $el.addClass('unstuck');
              } else {
                  $el.removeClass('unstuck');
              }
            },
            offset: 'bottom-in-view'
           });
        });
      };

      this._initNested();
      this._initDynamicTitle();
      this._initMediaAttachment();
      this._initEditor();
      this._initFollowActions();
    };
});

