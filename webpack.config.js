const path = require('path');
const Encore = require('@symfony/webpack-encore');

const SyliusAdmin = require('@sylius-ui/admin');
const SyliusShop = require('@sylius-ui/shop');

// Admin config
const adminConfig = SyliusAdmin.getWebpackConfig(path.resolve(__dirname));

// Shop config
const shopConfig = SyliusShop.getWebpackConfig(path.resolve(__dirname));

// App shop config
Encore
    .setOutputPath('public/build/app/shop')
    .setPublicPath('/build/app/shop')
    .addEntry('app-shop-entry', './assets/shop/entrypoint.js')
    .addAliases({
        '@vendor': path.resolve(__dirname, 'vendor'),
    })
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .enableStimulusBridge(path.resolve(__dirname, './assets/shop/controllers.json'))
;

const appShopConfig = Encore.getWebpackConfig();

appShopConfig.externals = Object.assign({}, appShopConfig.externals, { window: 'window', document: 'document' });
appShopConfig.name = 'app.shop';

Encore.reset();

// App admin config
Encore
    .setOutputPath('public/build/app/admin')
    .setPublicPath('/build/app/admin')
    .addEntry('app-admin-entry', './assets/admin/entrypoint.js')
    .addAliases({
        '@vendor': path.resolve(__dirname, 'vendor'),
    })
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .enableStimulusBridge(path.resolve(__dirname, './assets/admin/controllers.json'))
;

const appAdminConfig = Encore.getWebpackConfig();

appAdminConfig.externals = Object.assign({}, appAdminConfig.externals, { window: 'window', document: 'document' });
appAdminConfig.name = 'app.admin';

module.exports = [shopConfig, adminConfig, appShopConfig, appAdminConfig];
