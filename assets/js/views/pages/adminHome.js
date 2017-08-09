/*!
 * Base Core
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
 * License. If not, see https://atelierdisko.de/licenses.
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
