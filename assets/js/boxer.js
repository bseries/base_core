/*!
 * Boxer
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

define(['jquery'],
function($) {

  // There can always be just one modal at a time.
  return function Modal() {
    var _this = this;

    this.elements.container =

    $('body').prepend(
		'<div id="modal" class="hide">'
			+ '<div class="controls"><div class="close">×</div></div>'
			+ '<div class="content"></div>'
		+ '</div>'
		+ '<div id="modal-overlay" class="hide"></div>'
    );

    this.elements = {
      container: $(modal || '#modal'),
      overlay: $(overlay || '#modal-container')
    };

    this.destroy = function() {
      _this.elements.container.remove();
      _this.elements.overlay.remove();
    };
  };

  var elements = {
    modal: $('#modal'),
    overlay: $('#modal-overlay'),
    controls: $('#modal .controls'),
    content: $('#modal .content'),
    close: $('#modal .controls .close')
  };

  var init = function() {
    bindEvents();
  };

  var loading = function() {
    $(document).trigger('modal:isLoading');

    elements.controls.hide();
    elements.content.hide();

    elements.overlay.fadeIn(200, function() {
      elements.modal.show();
    });
  };

  var fill = function(content, modalClass) {
    elements.content.html(content);

    if (modalClass) {
      this.type(modalClass);
    }
    $(document).trigger('modal:newContent');
  };

  var type = function(modalClass) {
     elements.modal.addClass(modalClass);
  };

  var ready = function() {
    elements.overlay.fadeIn(200, function() {
      elements.controls.show();
      elements.modal.show();
      elements.content.show();

      $(document).trigger('modal:isReady');
    });
  };

  var close = function() {
    $(document).trigger('modal:isClosing');

    elements.modal.fadeOut(100);
    elements.overlay.fadeOut(100);

    elements.content.html('');
    elements.modal.removeClass();
  };

  var bindEvents = function() {
    elements.content.on('click', function(ev) {
      ev.stopPropagation();
    });
    elements.modal.on('click', close);
    elements.overlay.on('click', close);

    elements.close.click(function(e) {
      if (e.button !== 0) {
        return;
      }
      e.preventDefault();
      close();
    });

    /* Close modal on ESC key. */
    $(document).bind('keydown', function(e) {
      if (e.keyCode == 27) {
        close();
      }
      return true;
    });
  };

  return {
    elements: elements,
    type: type,
    init: init,
    fill: fill,
    ready: ready,
    close: close,
    loading: loading
  };
});

