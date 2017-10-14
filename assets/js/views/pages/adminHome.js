/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

require(['jquery', 'widgets', 'minigrid', 'domready!'], function($, Widgets, Minigrid) {
  'use strict';

  function calcGrid() {
    var dfr = new $.Deferred();

    Minigrid({
      container: '.widgets',
      item: '.widget',
      gutter: 20,
      skipWindowOnLoad: true,
      done: dfr.resolve
    });

    return dfr;
  }

  $('.widget').each(function() {
    var el = this;

    Widgets.factory(el).render().done(function() {
      calcGrid().done(function() {
        $(el).removeClass('loading');
      });
    });
  });
});
