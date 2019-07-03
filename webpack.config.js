const path = require('path');
const Encore = require('@symfony/webpack-encore');

// Shop config
Encore
  .setOutputPath('public/assets/shop/')
  .setPublicPath('/assets/shop')
  .addEntry('shop-main', './assets/shop/main.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader()
;

const shopConfig = Encore.getWebpackConfig();
shopConfig.resolve.alias['sylius/ui'] = path.resolve(__dirname, 'vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/js/');
shopConfig.name = 'shop';

Encore.reset();

// Admin config
Encore
  .setOutputPath('public/assets/admin/')
  .setPublicPath('/assets/admin')
  .addEntry('admin-main', './assets/admin/main.js')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader()
;

const adminConfig = Encore.getWebpackConfig();
adminConfig.resolve.alias['sylius/ui'] = path.resolve(__dirname, 'vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/js/');
adminConfig.externals = Object.assign({}, adminConfig.externals, { window: 'window', document: 'document' });
adminConfig.name = 'admin';

module.exports = [shopConfig, adminConfig];
