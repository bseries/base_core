/*!
 * Animation Sequence
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */
define(['jquery', 'compat!animationFrame'], function($) {

  // Represents a sequence of frames. This can
  // be a subset of all frames available. Listens
  // to animation:drain events on element.
  return function AnimationSequence() {
    var _this = this;

    this.fps = 12;

    this.loop = true;

    // Number of times we've run through all frames.
    this.looped = 0;

    // Holds the current interval id in
    // case there is an ongoing animation loop.
    this.interval = undefined;

    // The current frame number. May be of float type
    // use Math.floor() to get to current frame.
    this.currentFrame = 0;

    this.frames = [];

    this.element = undefined;

    // Indicator if the sequence is in drain mode and
    // will end as soon as possible.
    this.drain = false;

    this.init = function(element, frames, options) {
      _this.element = element;
      _this.frames = frames;

      options = $.extend({
        loop: _this.loop,
        fps: _this.fps
      }, options || {});

      _this.fps = options.fps;
      _this.loop = options.loop;

      // Listen on element for signals.
      _this.element.on('animation:drain', function() {
        _this.drain = true;
      });
      _this.element.on('animation:seek', function(ev, to) {
        _this.currentFrame = to;
      });
    };


    // Plays the animation until certain conditions are met.
    this.start = function() {
      var dfr = new $.Deferred();

      if (_this.drain) {
        dfr.resolve();
        return dfr;
      }

      // Initial.
      var frame = _this.frames[_this.currentFrame];

      _this.update(frame);
      _this.currentFrame++;

      _this.interval = setInterval(function() {
        frame = _this.frames[_this.currentFrame];

        if (!frame) {
          _this.looped++;

          // Reset sequence even we might stop, that way we get
          // a clean start - if needed.
          _this.currentFrame = 0;
          frame = _this.frames[_this.currentFrame];

          // Will stop looping if in drain mode or loop count has been reached.
          if (_this.drain || (_this.loop !== true && _this.looped >= _this.loop)) {
            // Break out of animation here and signal that we are done to outer code.
            dfr.resolve();
            clearInterval(_this.interval);
            return;
          }
        }

        _this.update(frame);
        _this.currentFrame++;
      }, (1 / _this.fps) * 1000);

      return dfr;
    };

    // Seeks to a frame in the sequence. Relative values
    // like '20%' can be used.
    this.seek = function(to) {
      if (to.indexOf('%') !== -1) {
        to = parseInt(to.replace('%', ''), 10);
        to = (to / 100) * (_this.frames.length - 1);
      }
      _this.currentFrame = Math.floor(to);
      var frame = _this.frames[_this.currentFrame];

      _this.update(frame);
    };

    // Updates the element with frame informations, effectively
    // switching out visible frames.
    this.update = function(frame) {
      requestAnimationFrame(function() {
         _this.element.css({
          'background-image': 'url(' + frame.url + ')',
          'background-position': '-' + (frame.offset) + 'px 0'
        });
      });
    };

  };
});