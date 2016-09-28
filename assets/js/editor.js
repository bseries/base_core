/*!
 * Editor
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
 * License. If not, see http://atelierdisko.de/licenses.
 */
define(['jquery', 'wysihtml5'],
function($, wysihtml5) {
  return function Editor() {
    var _this = this;

    // FIXME May at a later point allow for globalization.
    var _ = function(key) {
      return key;
    };

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
          // Load our iframe.css based  off the admin.css path. Overly qualidied
          // to prevent using the reset.css sheet here (which comes first).
          $('link[href*=css]:eq(1)').attr('href').replace(/(admin)/, 'iframe')
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
         '<a data-wysihtml5-command="bold" class="plugin-basic button">' + _('bold') +'</a>' +
         '<a data-wysihtml5-command="italic" class="plugin-basic button">' + _('italic') + '</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" class="plugin-headline button">' + _('H2') + '</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h3" class="plugin-headline button">' + _('H3') + '</a>' +
         '<a data-wysihtml5-command="formatInline" data-wysihtml5-command-value="big" class="plugin-size button">' + _('big') + '</a>' +
         '<a data-wysihtml5-command="formatInline" data-wysihtml5-command-value="small" class="plugin-size button">' + _('small') + '</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="blockquote" class="plugin-quote button">' + _('„quote“') + '</a>' +
         '<a data-wysihtml5-command="insertHTML" data-wysihtml5-command-value="<hr/>" class="plugin-line button">―</a>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="aside" class="plugin-aside button">' + _('marginal') + '</a>' +
         '<a data-wysihtml5-command="insertUnorderedList" class="plugin-list button">' + _('list') + '</a>' +
         '<a data-wysihtml5-command="createLink" class="plugin-link button">' + _('link') + '</a>' +
         '<div data-wysihtml5-dialog="createLink" style="display: none;">' +
            // Beware of validation and Chrome's "An invalid form control with name=' is not focusable.".
           '<input data-wysihtml5-dialog-field="href" type="text" placeholder="http://example.com" />' +
           '<a data-wysihtml5-dialog-action="save" class="button save">' + _('OK') + '</a>' +
         '</div>' +
         '<a data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="pre" class="plugin-verbatim button">' + _('verbatim') + '</a>' +
         '<a data-wysihtml5-command="undo" class="plugin-history button">' + _('undo') + '</a>' +
         '<a data-wysihtml5-command="redo" class="plugin-history button">' + _('redo') + '</a>' +
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
