/*!
 * Sorting Index
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
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

