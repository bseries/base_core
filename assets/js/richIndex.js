/*!
  //
  // Relative Date Times
  //

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
 * License. If not, see https://atelierdisko.de/licenses.
 */

define(['jquery', 'router', 'thingsLoaded', 'nprogress', 'underscore', 'qtip'],
function($, Router, ThingsLoaded, Progress, _) {

    return function RichIndex($element) {
      var _this = this;

      this.$element = $element;

      this.endpoint = $element.data('endpoint');

      this.currentOrderField = null;

      this.currentOrderDirection = 'desc';

      this.currentFilter = null;

      this.currentPage = 1;

      // Holds the API of qtip for current tooltips.
      this.imagesTooltips = null;

      this._initSorting = function() {
        _this.$element.on('click', 'thead .table-sort', function(ev) {
          var $th = $(this);
          var direction;

          if ($th.hasClass('desc')) {
            direction = 'asc';
          } else if ($th.hasClass('asc')) {
            direction = 'desc';
          } else {
            // Set defaults when clicking on a currently
            // not sorted col. Sort dates and flags desc and others
            // asc. So that newest come first and with flags
            // enabled come first by default.
            if ($th.hasClass('date') || $th.hasClass('flag')) {
              direction = 'desc';
            } else {
              direction = 'asc';
            }
          }

          _this.$element.find('thead .table-sort').removeClass('desc asc');
          $th.addClass(direction);

          _this._updateCurrent();
          _this._request();
        });
      };

      this._initPaging = function() {
        _this.$element.on('click', '.nav-paging a', function(ev) {
          ev.preventDefault();

          var $el = $(this);

          _this.$element.find('.nav-paging a').removeClass('active');
          $el.addClass('active');

          _this._updateCurrent();
          _this._request();
        });
      };

      this._initFiltering = function() {
        _this.$element.find('.table-search').on('keyup', _.debounce(function(ev) {

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
          _this._updateCurrent();

          // set page to 1 so that next request
          // will bring us to the first page but
          // keep pagination working.
          _this.currentPage = 1;

          _this._request();
        }, 300));
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
        var $active;

        $active = _this.$element.find('thead .table-sort').filter('.asc,.desc');
        _this.currentOrderField = $active.data('sort');
        _this.currentOrderDirection =  $active.hasClass('desc') ? 'desc' : 'asc';

        $active = _this.$element.find('.table-search');
        _this.currentFilter = $active.val();

        $active = _this.$element.find('.nav-paging .active');
        if ($active.length) {
          var match = $active.attr('href').match(/page:(\d+)/);
          _this.currentPage = match ? match[1] : 1;
        } else {
          _this.currentPage = 1;
        }
      };

      this._request = function() {
        Progress.start();

        var url = _this.endpoint
          .replace('__PAGE__', _this.currentPage)
          .replace('__ORDER_FIELD__', _this.currentOrderField)
          .replace('__ORDER_DIRECTION__', _this.currentOrderDirection)
          .replace('__FILTER__', _this.currentFilter);

        return $.get(url)
          .done(function(html) {
            _this._destroyImages();

            // tbody nav may disappear in certain results.
            _this.$element.find('tbody').remove();
            _this.$element.find('thead').after(
              $(html).find('.use-rich-index tbody')
            );

            // paging nav may disappear in certain results.
            _this.$element.find('.nav-paging').remove();
            _this.$element.find('table').after(
              $(html).find('.use-rich-index .nav-paging')
            );

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
      this._initPaging();
      this._initImages();
    };
});

