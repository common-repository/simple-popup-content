jQuery(document).ready(function($) {
  /*\
  |*|
  |*|  :: cookies.js ::
  |*|
  |*|  A complete cookies reader/writer framework with full unicode support.
  |*|
  |*|  Revision #1 - September 4, 2014
  |*|
  |*|  https://developer.mozilla.org/en-US/docs/Web/API/document.cookie
  |*|  https://developer.mozilla.org/User:fusionchess
  |*|  https://github.com/madmurphy/cookies.js
  |*|
  |*|  This framework is released under the GNU Public License, version 3 or later.
  |*|  http://www.gnu.org/licenses/gpl-3.0-standalone.html
  |*|
  |*|  Syntaxes:
  |*|
  |*|  * docCookies.setItem(name, value[, end[, path[, domain[, secure]]]])
  |*|  * docCookies.getItem(name)
  |*|  * docCookies.removeItem(name[, path[, domain]])
  |*|  * docCookies.hasItem(name)
  |*|  * docCookies.keys()
  |*|
  \*/
  var docCookies = {
    getItem: function (sKey) {
      if (!sKey) { return null; }
      return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
    },
    setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
      if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
      var sExpires = "";
      if (vEnd) {
        switch (vEnd.constructor) {
          case Number:
            sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
            break;
          case String:
            sExpires = "; expires=" + vEnd;
            break;
          case Date:
            sExpires = "; expires=" + vEnd.toUTCString();
            break;
        }
      }
      document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
      return true;
    },
    removeItem: function (sKey, sPath, sDomain) {
      if (!this.hasItem(sKey)) { return false; }
      document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "");
      return true;
    },
    hasItem: function (sKey) {
      if (!sKey) { return false; }
      return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
    },
    keys: function () {
      var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
      for (var nLen = aKeys.length, nIdx = 0; nIdx < nLen; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
      return aKeys;
    }
  };






  var popup_cookie_name = 'disable_popup_' + spc_ajax.popup_post_id;

  if (docCookies.hasItem(popup_cookie_name)) {
    return;
  }
  

  vex.defaultOptions.className = 'vex-theme-wireframe';
  vex.dialog.defaultOptions.showCloseButton = true;
  vex.dialog.defaultOptions.focusFirstInput = false;

  const data_for_user = {};
  data_for_user['action'] = 'return_simple_popup_content_results';
  data_for_user['tag'] = 'get_content';
  data_for_user['popup_post_id'] = spc_ajax.popup_post_id;

  var popup_content = jQuery("#simple-popup-content");


  vex.dialog.alert({
    message: popup_content,
    input: [
      '<label><input id="disable_popup" type="checkbox" value="" /> Do not show again?</label>'
    ],
    afterOpen: function() {
      var popup_cookie_name = 'disable_popup_' + spc_ajax.popup_post_id;
      // Send request for do not show session
      jQuery("#disable_popup").change(function(){ 
        setCookie( jQuery(this).prop('checked') ); 
      });
      
      function setCookie( checked ){
        if (checked) {
          docCookies.setItem(popup_cookie_name, "Popup Disabled", '"Wed, 19 Feb 2127 01:04:55 EST"', '/', null, false);
        } else {
          docCookies.removeItem(popup_cookie_name, "/", null);
        }
      }
    },
    callback: function(value) {

    },
    buttons: [
        $.extend({}, vex.dialog.buttons.YES, { text: 'Close' })
    ]
  });
});