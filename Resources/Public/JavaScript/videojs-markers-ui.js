/*! @name videojs-markers-ui @license Apache-2.0 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('global/document'), require('video.js')) :
  typeof define === 'function' && define.amd ? define(['global/document', 'video.js'], factory) :
  (global.videojsPlaylistUi = factory(global.document,global.videojs));
}(this, (function (document,videojs) { 'use strict';

  document = document && document.hasOwnProperty('default') ? document['default'] : document;
  videojs = videojs && videojs.hasOwnProperty('default') ? videojs['default'] : videojs;

  function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
  }

  var version = "1.0.0";

  var dom = videojs.dom || videojs;
  var registerPlugin = videojs.registerPlugin || videojs.plugin; // Array#indexOf analog for IE8

  var indexOf = function indexOf(array, target) {
    for (var i = 0, length = array.length; i < length; i++) {
      if (array[i] === target) {
        return i;
      }
    }

    return -1;
  }; // see https://github.com/Modernizr/Modernizr/blob/master/feature-detects/css/pointerevents.js


  var supportsCssPointerEvents = function () {
    var element = document.createElement('x');
    element.style.cssText = 'pointer-events:auto';
    return element.style.pointerEvents === 'auto';
  }();

  var defaults = {
    className: 'vjs-markersui',
    playOnSelect: false,
    supportsCssPointerEvents: supportsCssPointerEvents
  }; // we don't add `vjs-markersui-now-playing` in addSelectedClass
  // so it won't conflict with `vjs-icon-play
  // since it'll get added when we mouse out

  var addSelectedClass = function addSelectedClass(el) {
    el.addClass('vjs-selected');
  };

  var removeSelectedClass = function removeSelectedClass(el) {
    el.removeClass('vjs-selected');
  };

  var upNext = function upNext(el) {
    el.addClass('vjs-up-next');
  };

  var notUpNext = function notUpNext(el) {
    el.removeClass('vjs-up-next');
  };

  var Component = videojs.getComponent('Component');

  var MarkersMenuItem =
  /*#__PURE__*/
  function (_Component) {
    _inheritsLoose(MarkersMenuItem, _Component);

    function MarkersMenuItem(player, playlistItem, settings) {
      var _this;

      if (!playlistItem.item) {
        throw new Error('Cannot construct a MarkersMenuItem without an item option');
      }

      _this = _Component.call(this, player, playlistItem) || this;
      _this.item = playlistItem.item;

      _this.emitTapEvents();

      _this.on(['click', 'tap'], _this.jumpTo_);

      _this.on('keydown', _this.handleKeyDown_);

      return _this;
    }

    var _proto = MarkersMenuItem.prototype;

    _proto.handleKeyDown_ = function handleKeyDown_(event) {
      // keycode 13 is <Enter>
      // keycode 32 is <Space>
      if (event.which === 13 || event.which === 32) {
        this.jumpTo_();
      }
    };

    _proto.jumpTo_ = function jumpTo_(event) {
      this.player_.markers.current(indexOf(this.player_.markers.getMarkers(), this.item));
    };

    _proto.createEl = function createEl() {

      var item = this.options_.item;

      var li = videojs.dom.createEl('li', {}, {
        'data-marker-key': item.key
      });

      if (typeof item.data === 'object') {
        var dataKeys = Object.keys(item.data);
        dataKeys.forEach(function (key) {
          var value = item.data[key];
          li.dataset[key] = value;
        });
      }

      li.className = 'vjs-markersui-item';


      var titleContainerEl = document.createElement('div');
      titleContainerEl.className = 'vjs-markersui-title-container';
      li.appendChild(titleContainerEl);

      var titleEl = document.createElement('span');
      var titleText = item.text || this.localize('Untitled chapter');
      titleEl.className = 'vjs-markersui-name';
      titleEl.appendChild(document.createTextNode(titleText));
      titleEl.setAttribute('title', titleText);
      titleContainerEl.appendChild(titleEl);
      return li;
    };

    return MarkersMenuItem;
  }(Component);

  var MarkersMenu =
  /*#__PURE__*/
  function (_Component2) {
    _inheritsLoose(MarkersMenu, _Component2);

    function MarkersMenu(player, options) {
      var _this2;

      if (!player.markers) {
        throw new Error('videojs-markers is required for the markers UI component');
      }

      _this2 = _Component2.call(this, player, options) || this;
      _this2.items = [];


      _this2.addClass('vjs-markersui-vertical');
      // If CSS pointer events aren't supported, we have to prevent
      // clicking on playlist items during ads with slightly more
      // invasive techniques. Details in the stylesheet.


      if (options.supportsCssPointerEvents) {
        _this2.addClass('vjs-csspointerevents');
      }

      _this2.createMarkersMenu_();

      if (!videojs.browser.TOUCH_ENABLED) {
        _this2.addClass('vjs-mouse');
      }

      _this2.on(player, ['markersloaded','timeupdate'], function (event) {
        _this2.update();
      }); // Keep track of whether an ad is playing so that the menu
      // appearance can be adapted appropriately

      _this2.on('dispose', function () {
        _this2.empty_();

        player.markersMenu = null;
      });

      _this2.on(player, 'dispose', function () {
        _this2.dispose();
      });

      return _this2;
    }

    var _proto2 = MarkersMenu.prototype;

    _proto2.createEl = function createEl() {
      return dom.createEl('div', {
        className: this.options_.className
      });
    };

    _proto2.empty_ = function empty_() {
      if (this.items && this.items.length) {
        this.items.forEach(function (i) {
          return i.dispose();
        });
        this.items.length = 0;
      }
    };

    _proto2.createMarkersMenu_ = function createMarkersMenu_() {
      var markers = this.player_.markers.getMarkers() || [];

      var list = this.el_.querySelector('.vjs-markersui-item-list');

      if (!list) {
        list = document.createElement('ol');
        list.className = 'vjs-markersui-item-list';
        this.el_.appendChild(list);
      }

      this.empty_(); // create new items

      for (var i = 0; i < markers.length; i++) {
        var item = new MarkersMenuItem(this.player_, {
          item: markers[i]
        }, this.options_);
        this.items.push(item);
        list.appendChild(item.el_);
      }

      var selectedIndex = this.player_.markers.currentIndex();

      if (this.items.length && selectedIndex >= 0) {
        addSelectedClass(this.items[selectedIndex]);
      }

      if (this.items.length > 0) {

        this.player_.el().parentNode.className += ' vjs-markers-sidebar';
        this.player_.el().parentNode.appendChild(this.el_);

        let el = this.player_.el();
        let wrapper = document.createElement('div');
        wrapper.classList.add('vjs-player-wrapper');
        el.parentNode.insertBefore(wrapper, el);
        wrapper.appendChild(el);

      }


    };

    _proto2.update = function update() {
      // replace the marker items being displayed, if necessary
      var markers = this.player_.markers.getMarkers();

      if (this.items.length !== markers.length) {
        // if the menu is currently empty or the state is obviously out
        // of date, rebuild everything.
        this.createMarkersMenu_();
        return;
      }

      for (var i = 0; i < this.items.length; i++) {
        if (this.items[i].item !== markers[i]) {
          // if any of the markers items have changed, rebuild the
          // entire markers
          this.createMarkersMenu_();
          return;
        }
      } // the markers itself is unchanged so just update the selection


      var currentIndex = this.player_.markers.currentIndex() - 1;

      for (var _i = 0; _i < this.items.length; _i++) {
        var item = this.items[_i];

        if (_i === currentIndex) {
          addSelectedClass(item);


          notUpNext(item);
        } else if (_i === currentIndex + 1) {
          removeSelectedClass(item);
          upNext(item);
        } else {
          removeSelectedClass(item);
          notUpNext(item);
        }
      }
    };

    return MarkersMenu;
  }(Component);
  /**
   * Returns a boolean indicating whether an element has child elements.
   *
   * Note that this is distinct from whether it has child _nodes_.
   *
   * @param  {HTMLElement} el
   *         A DOM element.
   *
   * @return {boolean}
   *         Whether the element has child elements.
   */


  var hasChildEls = function hasChildEls(el) {
    for (var i = 0; i < el.childNodes.length; i++) {
      if (dom.isEl(el.childNodes[i])) {
        return true;
      }
    }

    return false;
  };
  /**
   * Finds the first empty root element.
   *
   * @param  {string} className
   *         An HTML class name to search for.
   *
   * @return {HTMLElement}
   *         A DOM element to use as the root for a playlist.
   */


  var findRoot = function findRoot(className) {
    var all = document.querySelectorAll('.' + className);
    var el;

    for (var i = 0; i < all.length; i++) {
      if (!hasChildEls(all[i])) {
        el = all[i];
        break;
      }
    }

    return el;
  };
  /**
   * Initialize the plugin on a player.
   *
   * @param  {Object} [options]
   *         An options object.
   *
   * @param  {HTMLElement} [options.el]
   *         A DOM element to use as a root node for the playlist.
   *
   * @param  {string} [options.className]
   *         An HTML class name to use to find a root node for the playlist.
   *
   * @param  {boolean} [options.playOnSelect = false]
   *         If true, will attempt to begin playback upon selecting a new
   *         playlist item in the UI.
   */


  var markersUi = function markersUi(options) {
    var player = this;

    if (!player.markers) {
      throw new Error('videojs-markers is required for the markers UI component');
    }

    options = videojs.mergeOptions(defaults, options); // If the player is already using this plugin, remove the pre-existing
    // MarkersMenu, but retain the element and its location in the DOM because
    // it will be re-used.

    if (player.markersMenu) {

      var el = player.markersMenu.el(); // Catch cases where the menu may have been disposed elsewhere or the
      // element removed from the DOM.

      if (el) {
        var parentNode = el.parentNode;
        var nextSibling = el.nextSibling; // Disposing the menu will remove `el` from the DOM, but we need to
        // empty it ourselves to be sure.

        player.markersMenu.dispose();
        dom.emptyEl(el); // Put the element back in its place.

        if (nextSibling) {
          parentNode.insertBefore(el, nextSibling);
        } else {
          parentNode.appendChild(el);
        }

        options.el = el;
      }
    }

    if (!dom.isEl(options.el)) {
      options.el = findRoot(options.className);
    }

    player.markersMenu = new MarkersMenu(player, options);
  }; // register components


  videojs.registerComponent('MarkersMenu', MarkersMenu);
  videojs.registerComponent('MarkersMenuItem', MarkersMenuItem); // register the plugin

  registerPlugin('markersUi', markersUi);
  markersUi.VERSION = version;

  return markersUi;

})));
