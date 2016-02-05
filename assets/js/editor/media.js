/*!
 * Editor
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
define([
  'jquery', 'wysihtml5', 'mediaExplorerModal', 'router'
], function(
  $, wysihtml5, MediaExplorerModal, Router
) {

  // This works in tandem with the Editor helper,
  // which enables dynamic image version replacement.
  return function EditorMedia() {

    var _this = this;

    this.mediaExplorerConfig = {
      'available': {
        // FIXME Restrict to images only.
        'selectable': true,
      }
    };

    this.init = function(options) {
      _this.mediaExplorerConfig = $.extend(_this.mediaExplorerConfig, options || {});
      MediaExplorerModal.init(_this.mediaExplorerConfig);

      return _this;
    };

    this.item = function(id) {
      return Router.match('media:view', {id: id})
        .then(function(url) {
            return $.getJSON(url);
        });
    };

    this.toolbar = function() {
      return '<a data-wysihtml5-command="insertMedia" class="button media-explorer">' + 'media' + '</a>';
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
              image = doc.createElement('IMG');

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
