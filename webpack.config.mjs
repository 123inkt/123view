import Encore from '@symfony/webpack-encore';
import { createRequire } from 'module';
const require = createRequire(import.meta.url);
const coreJsVersion = require('core-js/package.json').version;

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
        .configureBabel((babelConfig) => {
            babelConfig.plugins.push(['polyfill-corejs3', { method: 'usage-global', version: coreJsVersion }]);
        })
        .enableSassLoader()
        .enableTypeScriptLoader()
;

export default await Encore.getWebpackConfig();
