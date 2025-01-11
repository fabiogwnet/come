// Consts
const PLUGIN_NAME = 'a2c-prefixer';

function a2cPrefixer(css, opts) {
   css.eachDecl(function(decl) {
      var declParent =  decl.parent;

      if (decl.prop === 'opacity') {
          declParent
          .insertAfter(decl, {
              prop: '-ms-filter',
              value: '"progid:DXImageTransform.Microsoft.Alpha(Opacity=' + (parseFloat(decl.value) * 100) + ')"'
          })
          .insertAfter(decl, {
              prop: 'filter',
              value: 'alpha(opacity='+ (parseFloat(decl.value) * 100) +')'
          });
      }

      if (decl.prop === 'display' && decl.value === 'inline-block') {
        declParent
        .insertAfter(decl, {
              prop: '*display',
              value: 'inline'
        })
        .insertAfter(decl, {
              prop: '*zoom',
              value: '1'
        });
      }

  });
}

// Exporting the plugin main function
module.exports = a2cPrefixer;