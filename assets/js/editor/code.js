/*!
 * Editor
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
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
  'jquery', 'wysihtml5'
], function(
  $, wysihtml5
) {

  return function EditorCode() {

    var _this = this;

    this.init = function(options) {
      return _this;
    };

    this.toolbar = function() {
      return '<a data-wysihtml5-command="formatCode" class="button plugin-code">' + 'code' + '</a>';
    };

    this.classes = function() {
      return {
        'rt__code': 1
      };
    };

    this.tags = function() {
      return {
        code: {
          "set_class": "rt__code",
          "check_attributes": {
            "class": "class"
          }
        }
      };
    };

    this.commands = function() {
      return {
        formatCode: {
          exec: function(composer) {
            var pre;

            pre = this.state(composer);
            if (pre) {
              // caret is already within a <pre><code>...</code></pre>
              composer.selection.executeAndRestore(function() {
                var code = pre.querySelector("code");
                wysihtml5.dom.replaceWithChildNodes(pre);
                if (code) {
                  wysihtml5.dom.replaceWithChildNodes(pre);
                }
              });
            } else {
              // Wrap in <pre><code>...</code></pre>
              var range = composer.selection.getRange(),
                  selectedNodes = range.extractContents(),
                  code = composer.doc.createElement("code");

              pre = composer.doc.createElement("pre");
              pre.appendChild(code);
              code.appendChild(selectedNodes);
              range.insertNode(pre);
              composer.selection.selectNode(pre);
            }
          },
          state: function(composer) {
            var selectedNode = composer.selection.getSelectedNode();
            return wysihtml5.dom.getParentElement(selectedNode, { nodeName: "CODE" }) && wysihtml5.dom.getParentElement(selectedNode, { nodeName: "PRE" });
          }
        }
      };
    };
  };
});
