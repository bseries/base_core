/*!
 * Copyright 2017 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */
define('translator', ['domready!'], function() {
  'use strict';

  var locale = document.querySelector('html').getAttribute('lang');

  return function Translator(translations) {
    this.translate = function(key) {
      if (locale in translations) {
        if (key in translations[locale]) {
          return translations[locale][key];
        }
      }
      return key;
    };
  };
});
