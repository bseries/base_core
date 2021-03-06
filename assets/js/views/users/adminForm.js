/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

require(['jquery', 'domready!'], function($) {
  'use strict';

  $('#UsersChangePassword').on('change', function(ev) {
    $('#UsersPassword').prop('disabled', !$(this).is(':checked'));
  });
  $('#UsersChangeAnswer').on('change', function(ev) {
    $('#UsersAnswer').prop('disabled', !$(this).is(':checked'));
  });
});
