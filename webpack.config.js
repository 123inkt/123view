const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .addEntry('app', './assets/app.ts')
        .addEntry('app.dark', './assets/app.dark.ts')
        .addEntry('app.light', './assets/app.light.ts')
        .addEntry('bs.theme', './assets/bootstrap-theme.ts')
        .enableStimulusBridge('./assets/controllers.json')
        .splitEntryChunks()
        .enableSingleRuntimeChunk()
        .cleanupOutputBeforeBuild()
        .enableSourceMaps(!Encore.isProduction())
        .enableVersioning(Encore.isProduction())
        .configureBabelPresetEnv((config) => {
            config.useBuiltIns = 'usage';
            config.corejs      = '3.26';
        })
        .enableSassLoader()
        .enableTypeScriptLoader()
;

module.exports = Encore.getWebpackConfig();
