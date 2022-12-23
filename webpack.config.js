const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .addEntry('app', './assets/app.ts')
        .enableStimulusBridge('./assets/controllers.json')
        .splitEntryChunks()
        .enableSingleRuntimeChunk()
        .cleanupOutputBeforeBuild()
        .enableBuildNotifications()
        .enableSourceMaps(!Encore.isProduction())
        .enableVersioning(Encore.isProduction())
        .configureBabelPresetEnv((config) => {
            config.useBuiltIns = 'usage';
            config.corejs      = '3.23';
        })
        .enableSassLoader()
        .enableTypeScriptLoader()
;

module.exports = Encore.getWebpackConfig();
