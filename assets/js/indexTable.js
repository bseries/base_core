/*!
 * Index Table
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

define(['jquery', 'router', 'thingsLoaded', 'nprogress', 'qtip', 'domready!'],
function($, Router, ThingsLoaded, Progress) {

    return function IndexTable($element) {
      var _this = this;

      this.$element = $element;

      this.sortableFields = [];

      this.endpoints = {
        sort: null
      };

      // Holds the API of qtip for current tooltips.
      this.imagesTooltips = null;

      this._initSorting = function() {
        _this.$element.find('thead .table-sort').each(function() {
          _this.sortableFields.push($(this));
        });
        _this.endpoints.sort = _this.$element.data('endpoint-sort');

        $.each(_this.sortableFields, function(k, $th) {
          $th.on('click', function(ev) {
            var direction = $th.hasClass('desc') ? 'asc' : 'desc';

            _this.$element.find('thead td').removeClass('desc asc');
            $th.addClass(direction);

            _this._requestSort($th.data('sort'), direction);
          });
        });
      };

      this._requestSort = function(field, direction) {
        var url = _this.endpoints.sort
          .replace('__ORDER_FIELD__', field)
          .replace('__ORDER_DIRECTION__', direction);

        return $.get(url)
          .done(function(html) {
            _this._destroyImages();

            _this.$element.find('tbody').replaceWith(
              $(html).find('.use-index-table tbody')
            );
            history.pushState(null,null, url);
            _this._initImages();
          });
      };

      this._destroyImages = function() {
        if (_this.imagesTooltips) {
          _this.imagesTooltips.destroy(true);
        }
      };

      // Enlarge images when hovering over them in a table.
      this._initImages = function() {
        var $img = _this.$element.find('tbody td.media img');

        if ($img.length) {
          return true;
        }
        var tooltips = $img.qtip({
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

              return 'Loading…';
            }
          }
        });

        _this.imagesTooltips = tooltips.qtip('api');
      };

      this._initSorting();
      this._initImages();

    };

});

