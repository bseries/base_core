/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

require(['jquery', 'domready!'], function($) {
  'use strict';

  if (App.flash) {
    require(['notify'], function() {
      $.notify(App.flash.message, {
        level: App.flash.attrs.level || 'neutral',
        timeout: 3000
      });
    });
  }

  $('button[type=submit]').on('click', function(ev) {
    $(this).addClass('loading');
  });
});
