/*!
 * Base Core
 *
 * Copyright (c) 2017 Atelier Disko - All rights reserved.
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
