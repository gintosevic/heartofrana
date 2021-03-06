(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(factory);
  } else if (typeof exports === 'object') {
    module.exports = root.document ? factory(root, root.document) : function(w){ return factory(w, w.document) };
  } else {
    root.SVG = factory(root, root.document);
  }
}(typeof window !== "undefined" ? window : this, function(window, document) {

<%= contents %>

return SVG

}));