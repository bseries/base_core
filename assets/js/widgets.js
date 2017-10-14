/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

define([
  'jquery',
  'router',
  'handlebars',
  'text!base-core/js/templates/widgets/counter.hbs',
  'text!base-core/js/templates/widgets/table.hbs',
  'text!base-core/js/templates/widgets/quickdial.hbs',
  'domready!'
], function(
  $,
  Router,
  Handlebars,
  CounterTemplate,
  TableTemplate,
  QuickdialTemplate
) {
  'use strict';

  // TODO Handle timeout and error gracefully

  // Base class for all widgets.
  function Widget(element, name) {
    var _this = this;

    this.$element = $(element);
    this.name = name;

    this.data = function() {
      var dfr = new $.Deferred();

      Router.match('widgets:view', {id: _this.name})
        .done(function(url) {
          $.ajax({url: url, dataType: 'json'}).done(function(data) {
            dfr.resolve(data.data);
          });
      });

      return dfr;
    };

    // Subclasses must overrride this method.
    this.render = function() {};
  }

  // The counter widget has at least a title and can
  // have one or multiple counter groups.
  function CounterWidget(element, name) {
    Widget.call(this, element, name);
    var _this = this;

    this.render = function() {
      var template = Handlebars.compile(CounterTemplate);

      return _this.data()
        .done(function(data) {
          _this.$element.html(template(data));
          if (data["class"]) {
            _this.$element.addClass(data["class"]);
          }
          _this.$element.addClass('widget-counter');
          _this.$element.removeClass('loading');
        });
    };
  }
  CounterWidget.prototype = Object.create(Widget.prototype);

  // The table widget has two columns one for the title
  // and one for arbitrary counts.
  // FIXME Sort lines by title.
  function TableWidget(element, name) {
    Widget.call(this, element, name);
    var _this = this;

    this.render = function() {
      var template = Handlebars.compile(TableTemplate);

      return _this.data()
        .done(function(data) {
          if ($.isEmptyObject(data.data) || $.isArray(data.data) && data.data.length === 0)  {
            _this.$element.remove();
          } else {
            _this.$element.html(template(data));
            _this.$element.addClass('widget-table');
            _this.$element.removeClass('loading');
          }
        });
    };
  }
  TableWidget.prototype = Object.create(Widget.prototype);

  // The quickidal widget is just one single big link.
  function QuickdialWidget(element, name) {
    Widget.call(this, element, name);
    var _this = this;

    this.render = function() {
      var template = Handlebars.compile(QuickdialTemplate);

      return _this.data()
        .done(function(data) {
          _this.$element.html(template(data));
          _this.$element.addClass('widget-quickdial widget-gamma');
          _this.$element.removeClass('loading');
        });
    };
  }
  QuickdialWidget.prototype = Object.create(Widget.prototype);

  //
  // Export / Public Interface
  //
  var map = {
    Counter: CounterWidget,
    Table: TableWidget,
    Quickdial: QuickdialWidget,
  };
  return $.extend(map, {
    factory: function(element) {
      var camelize = function(value) {
        return value.replace (/(?:^|[-_])(\w)/g, function (_, c) {
          return c ? c.toUpperCase () : '';
        });
      };

      var type = camelize($(element).data('widget-type'));
      var name = $(element).data('widget-name');

      return new map[type](element, name);
    }
  });
});
