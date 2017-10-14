/*!
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

define([
  'jquery',
  'translator',
  'jqueryUi',
  'notify'
], function(
  $,
  Translator
) {
  'use strict';

  return function SortableIndex(container) {
    var t = (new Translator({
      "de": {
        "Order saved.": "Sortierung gespeichert.",
        "Failed to save order.": "Speichern der Sortierung fehlgeschlagen.",
        "Ensure your adblocker is switched off.": "Stellen Sie sicher, dass ihr Adblocker deaktiviert ist.",
      }
    })).translate;

    $(container).sortable({
      placeholder: 'sortable-placeholder',
      items: '> tr',
      update: function(ev, ui) {
        var ids = [];
        $(container).find('tr').each(function(k, v) {
          ids.push($(v).data('id'));
        });
        $.ajax({
          type: 'POST',
          // Assumes we are on an index page and can relatively get to the endpoint.
          //
          // FIXME When defining the class, the endpoint should be defined in HTML as
          //       a data attribute.
          url: window.location.pathname.replace('/admin', '/admin/api') + '/order',
          data: {'ids': ids},
        }).done(function() {
          $.notify(t('Order saved.'), {level: 'success'});
        }).fail(function() {
          $.notify(t('Failed to save order.'), {level: 'error'});
          $.notify(t('Ensure your adblocker is switched off.'), {level: 'notice'});
        });
      }
    });
  };
});

