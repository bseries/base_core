/*!
 * Copyright 2013 David Persson. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */
define('scrollTo', ['jquery'], function($) {

  function toOffsets(x, y, speed, easing) {
    var result = new $.Deferred();

    $('html, body').animate(
      {
        scrollTop: y,
        scrollLeft: x
      },
      speed || 'normal',
      easing || 'swing',
      result.resolve
    );

     return result;
  }

  function toElement(element, speed, easing) {
    var offset = $(element).offset();

    return toOffsets(
      offset.left,
      offset.top,
      speed,
      easing
    );
  }

  return {
    element: toElement,
    offsets: toOffsets
  };
});
