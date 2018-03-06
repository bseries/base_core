/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */
define([
  'jquery',
  'translator',
  'wysihtml5',
  'mediaExplorerModal',
  'router'
], function(
  $,
  Translator,
  wysihtml5,
  MediaExplorerModal,
  Router
) {
  'use strict';

  // This works in tandem with the Editor helper,
  // which enables dynamic image version replacement.
  return function EditorMedia() {

    var _this = this;

    var t = (new Translator({
      "de": {
        "media": "Medien",
      }
    })).translate;

    this.init = function(options) {
      Router.match('media:capabilities')
        .done(function(url) {
          $.getJSON(url)
          .done(function(res) {

            MediaExplorerModal.init($.extend(options || {}, {
              'transfer': res.data.transfer
            }));
          });
        });

      return _this;
    };

    this.item = function(id) {
      return Router.match('media:view', {id: id})
        .then(function(url) {
            return $.getJSON(url);
        });
    };

    this.toolbar = function() {
      return '<a data-wysihtml5-command="insertMedia" class="button media-explorer">' + t('media') + '</a>';
    };

    this.classes = function() {
      return {
        'media': 1,
        'image': 1
      };
    };

    this.tags = function() {
      return {
        "img": {
          "check_attributes": {
            "width": "numbers",
            "height": "numbers",
            "class": "class",
            "alt": "alt",
            "src": "src",
            "data-media-id": "numbers"
          }
        }
      };
    };

    this.commands = function() {
      return {
        insertMedia: {
          exec: function(composer, command, value) {
            var doc = composer.doc;

            MediaExplorerModal.open();

            var insert = function(data) {
              var image = doc.createElement('IMG');

              image.setAttribute('src', data.versions.fix2admin.url);
              image.setAttribute('alt', 'image');
              image.setAttribute('title', data.title);
              image.setAttribute('data-media-id', data.id);

              composer.selection.insertNode(image);
            };

            $(document).one('media-explorer:selected', function(ev, ids) {
              $.each(ids, function(k, id) {
                _this.item(id).done(function(data) {
                  insert(data.data.file);
                });
              });
              MediaExplorerModal.close();
            });

          },
          state: function(composer) {
            wysihtml5.commands.insertImage.state(composer);
          }
        }
      };
    };
  };
});
