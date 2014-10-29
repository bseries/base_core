/*!
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

require(['jquery', 'notify', 'domready!'], function($) {

  //
  // Bridge between PHP flash messaging and JS notify.
  //
  var flashMessage = $('#messages').data('flash-message');
  var flashLevel = $('#messages').data('flash-level') || 'neutral';

  if (flashMessage) {
    $.notify(flashMessage, {level: flashLevel});
  }
});
