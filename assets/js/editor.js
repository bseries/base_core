/*!
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */
define(['jquery', 'translator', 'wysihtml5'],
function($, Translator, wysihtml5) {
  'use strict';

  return function Editor() {
    var _this = this;

    var t = (new Translator({
      "de": {
        "bold": "fett",
        "italic": "kursiv",
        "big": "groß",
        "small": "klein",
        "link": "Link",
        "H2": "Ü2",
        "H3": "Ü3",
        "„quote“": "„Zitat“",
        "marginal": "Marginalie",
        "list": "Liste",
        "clear format": "Formatierung entfernen",
      }
    })).translate;

    this.rules = {
      // Classes are prefix with rt (richtext)
      // to make them separatly stylable.
      classes: {
        'rt__big': 1,
        'rt__small': 1,
        'rt__h--beta': 1,
        'rt__h--gamma': 1,
        'rt__aside': 1,
        'rt__verbatim': 1,
      },
      tags: {
        "aside":  { "set_class": "rt__aside" },
        span: {},
        "big": {
            "rename_tag": "span",
            "set_class": "rt__big"
        },
        "small": {
            "rename_tag": "span",
            "set_class": "rt__small"
        },
        h2: {
          "set_class": "rt__h--beta",
          "check_attributes": {
            "class": "class"
          }
        },
        h3: {
          "set_class": "rt__h--gamma",
          "check_attributes": {
            "class": "class"
          }
        },
        div: {
          "check_attributes": {
            "class": "class"
          }
        },
        pre: {
          "set_class": "rt__verbatim",
          "check_attributes": {
            "class": "class"
          }
        },
        dl: {},
        dt: {},
        dd: {},
        hr: {},
        blockquote: {},
        strong: { rename_tag: "b" }, // User intends to style visually not semantically.
        b:      {},
        i:      {},
        em:     { rename_tag: "i" }, // User intends to style visually not semantically.
        br:     {},
        p:      {},
        ul:     {},
        ol:     {},
        li:     {},
        a:      {
          check_attributes: {
            href:   'href' // important to avoid XSS, allows all kinds of URLs (absolute, schema-less)
          }
        }
      }
    };

    this.plugins = [];

    this.id = null;

    this.elements = {
      main: null,
      wrap: null
    };

    this.init = function(element, plugins) {
      _this.plugins = plugins;

      // This needs to be a textarea element.
      _this.elements.main = $(element);
      _this.id = _this.elements.main.attr('id');

      // The textarea element is assumed to be wrapped by a div.
      _this.elements.wrap = _this.elements.main.parent();

      _this.initPlugins();

      _this.elements.wrap.addClass('editor');
      _this.elements.main.hide();

      var html = _this.renderToolbar();
      html = $(html).hide();
      _this.elements.wrap.find('label').after(html);
      html.fadeIn();

      _this.attachEditor();
    };

    this.initPlugins = function() {
      $.each(_this.plugins, function(k, plugin) {
        if (typeof plugin === 'string') {
          // ...
        } else {
          _this.rules.classes = $.extend(_this.rules.classes, plugin.classes());
          _this.rules.tags = $.extend(_this.rules.tags, plugin.tags());

          // Need to add to global wysihtml object.
          wysihtml5.commands = $.extend(wysihtml5.commands, plugin.commands());
        }
      });
    };

    this.attachEditor = function() {
      new wysihtml5.Editor(_this.id, {
        toolbar: _this.id + "Toolbar",
        parserRules: _this.rules,
        autoLink: true,
        // No bloat.
        style: false,
        handleTables: false,
        bodyClassName: null,
        // Use <p> when hitting enter and <br> for shift+enter.
        useLineBreaks: false,
        stylesheets: [
          // Load our iframe.css based off the base.css path.
          $('link[href*=css]:eq(0)').attr('href').replace(/(base.css)/, 'iframe.css')
        ]
      });
      // There is no way to disable the creation of this "helper" field
      // inside wysi. However it messes with nested arrays in our forms.
      // Especially when using i18n.
      $('input[name=_wysihtml5_mode]').remove();
    };

    this.renderToolbar = function() {
      var html = $('' +
      '<div id="' + _this.id + 'Toolbar" class="toolbar" style="display: none;">' +
         '<a data-wysihtml5-command="bold" class="plugin-basic button">' + t('bold') +'</a>' +
         '<a data-wysihtml5-command="italic" class="plugin-basic button">' + t('italic') + '</a>' +
         '<a data-wysihtml5-command="formatInline" data-wysihtml5-command-value="big" class="plugin-size button">' + t('big') + '</a>' +
         '<a data-wysihtml5-command="formatInline" data-wysihtml5-command-value="small" class="plugin-size button">' + t('small') + '</a>' +
         '<a data-wysihtml5-command="createLink" class="plugin-link button">' + t('link') + '</a>' +
         '<div data-wysihtml5-dialog="createLink" style="display: none;">' +
            // Beware of validation and Chrome's "An invalid form control with name=' is not focusable.".
           '<input data-wysihtml5-dialog-field="href" type="text" placeholder="http://example.com" />' +
           '<a data-wysihtml5-dialog-action="save" class="button save">' + t('OK') + '</a>' +
         '</div>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" class="plugin-headline button">' + t('H2') + '</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h3" class="plugin-headline button">' + t('H3') + '</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="blockquote" class="plugin-quote button">' + t('„quote“') + '</a>' +
         '<a data-wysihtml5-command="insertHTML" data-wysihtml5-command-value="<hr/>" class="plugin-line button">―</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="aside" class="plugin-aside button">' + t('marginal') + '</a>' +
         '<a data-wysihtml5-command="insertUnorderedList" class="plugin-list button">' + t('list') + '</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="pre" class="plugin-verbatim button">' + t('verbatim') + '</a>' +
         '<a data-wysihtml5-command="undo" class="plugin-history button"><i class="material-icons">undo</i></a>' +
         '<a data-wysihtml5-command="redo" class="plugin-history button"><i class="material-icons">redo</i></a>' +
         '<a data-wysihtml5-command="removeFormat" class="plugin-basic button">' + t('clear format') + '</a>' +
       '</div>');

      var builtin = [];
      var external = [];

      $.each(_this.plugins, function(k, plugin) {
        if (typeof plugin === 'string') {
          builtin.push('.plugin-' + plugin);
        } else {
          external.push(plugin);
        }
      });

      // Prevent removing nested a's (i.e. dialog buttons).
      html.find('> a').not(builtin.join()).remove();

      $.each(external, function(k, plugin) {
          html.append(plugin.toolbar());
      });
      return html;
    };
  };
});
