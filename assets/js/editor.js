/*!
 * Editor
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */
define(['jquery', 'wysihtml5', 'mediaExplorerModal'],
function($, wysihtml5, MediaExplorerModal) {
  return function Editor() {
    var _this = this;

    // FIXME May at a later point allow for globalization.
    var _ = function(key) {
      return key;
    };

    this.rules = {
      classes: {
        'big': 1,
        'small': 1,
        'beta': 1,
        'gamma': 1
      },
      tags: {
        span: {},
        "big": {
            "rename_tag": "span",
            "set_class": "big"
        },
        "small": {
            "rename_tag": "span",
            "set_class": "small"
        },
        h2: {
          "set_class": "h-beta",
          "check_attributes": {
            "class": "class"
          }
        },
        h3: {
          "set_class": "h-gamma",
          "check_attributes": {
            "class": "class"
          }
        },
        div: {
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
            href:   'url' // important to avoid XSS
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
      instance = new wysihtml5.Editor(_this.id, {
        toolbar: _this.id + "Toolbar",
        style: false,
        parserRules: _this.rules,
        autoLink: false,
        bodyClassName: null,
        composerClassName: 'composer',
        stylesheets: [
          // skip reset stylesheet
          $('link[href*=css]:eq(1)').attr('href').replace(/(admin)/, 'iframe')
        ]
      });

      // instance.on('load', function() {
      //  _this.elements.main.trigger('editor:loaded');
      // });
      return instance;
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
         '<a data-wysihtml5-command="insertUnorderedList" class="plugin-list button">' + _('list') + '</a>' +
         '<a data-wysihtml5-command="createLink" class="plugin-link button">' + _('link') + '</a>' +
         '<a data-wysihtml5-command="undo" class="plugin-history button">' + _('undo') + '</a>' +
         '<a data-wysihtml5-command="redo" class="plugin-history button">' + _('redo') + '</a>' +
         '<div data-wysihtml5-dialog="createLink" style="display: none;">' +
           '<input data-wysihtml5-dialog-field="href" value="http://" class="text" />' +
           '<a data-wysihtml5-dialog-action="save" class="button">' + _('OK') + '</a><a data-wysihtml5-dialog-action="cancel" class="button">' + _('cancel') + '</a>' +
         '</div>' +
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

      html.find('a').not(builtin.join()).remove();

      $.each(external, function(k, plugin) {
          html.append(plugin.toolbar());
      });
      return html;
    };
  };
});
