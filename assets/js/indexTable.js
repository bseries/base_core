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

define(['jquery', 'router', 'thingsLoaded', 'nprogress', 'qtip'],
function($, Router, ThingsLoaded, Progress) {

    return function IndexTable($element) {
      var _this = this;

      this.$element = $element;

      this.endpoint = $element.data('endpoint');

      this.currentOrderField = null;

      this.currentOrderDirection = 'desc';

      this.currentFilter = null;

      // Holds the API of qtip for current tooltips.
      this.imagesTooltips = null;

      this._initSorting = function() {
        _this.$element.on('click', 'thead .table-sort', function(ev) {
            var $th = $(this);
            var direction = $th.hasClass('desc') ? 'asc' : 'desc';

            _this.$element.find('thead .table-sort').removeClass('desc asc');
            $th.addClass(direction);

            _this._request();
        });
      };

      this._initFiltering = function() {
        _this.$element.find('.table-search').on('keyup', function(ev) {
          // set page to 1 so that next request
          // will bring us to the first page but
          // keep pagination working.

          _this.endpoint = _this.endpoint.replace(/page\:\d+/, 'page:1');

          var skip = [
            9, /* tab */
            13, /* enter */
            16, /* shift */
            17, /* ctrl */
            18, /* alt */
            36, /* home */
            37, /* left */
            39, /* right */
            38, /* up */
            40, /* down */
          ];
          if ($.inArray(ev.keyCode, skip) != -1) {
            return;
          }
          if (ev.keyCode == 27) { /* ESC */
            $(this).val('');
          }
          _this._request();
        });
      };

      // Enlarge images when hovering over them in a table.
      this._initImages = function() {
        var $img = _this.$element.find('tbody td.media img');

        if (!$img.length) {
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

      this._updateCurrent = function() {
        var $active = _this.$element.find('thead .table-sort').filter('.asc,.desc');

        _this.currentOrderField = $active.data('sort');
        _this.currentOrderDirection =  $active.hasClass('desc') ? 'asc' : 'desc';
        _this.currentFilter = _this.$element.find('.table-search').val();
      };

      this._request = function() {
        _this._updateCurrent();
        Progress.start();

        var url = _this.endpoint
          .replace('__ORDER_FIELD__', _this.currentOrderField)
          .replace('__ORDER_DIRECTION__', _this.currentOrderDirection)
          .replace('__FILTER__', _this.currentFilter);

        return $.get(url)
          .done(function(html) {
            _this._destroyImages();

            _this.$element.find('tbody').replaceWith(
              $(html).find('.use-index-table tbody')
            );

            // paging nav may disappear in certain results.
            _this.$element.find('.nav-paging').remove();
            _this.$element.find('table').after(
              $(html).find('.use-index-table .nav-paging')
            );

            // Update endpoint so we keep order when paging.
            // _this.endpoint = $(html).find('.use-index-table').data('endpoint');

            history.pushState(null,null, url);
            _this._initImages();
          })
          .always(function() {
            Progress.done(true);
          });
      };

      this._destroyImages = function() {
        if (_this.imagesTooltips) {
          _this.imagesTooltips.destroy(true);
        }
      };

      this._initSorting();
      this._initFiltering();
      this._initImages();
    };
});

