import {HttpClient, provideHttpClient, withInterceptors} from '@angular/common/http';
import {ApplicationConfig, importProvidersFrom, provideBrowserGlobalErrorListeners, provideZoneChangeDetection} from '@angular/core';
import {provideRouter} from '@angular/router';
import {httpclientUrlInterceptor} from '@interceptor/httpclient-url-interceptor';
import {TranslateLoader, TranslateModule} from '@ngx-translate/core';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {CookieService} from 'ngx-cookie-service';
import {routes} from './app.routes';

export const appConfig: ApplicationConfig = {
    providers: [
        provideBrowserGlobalErrorListeners(),
        provideZoneChangeDetection({eventCoalescing: true}),
        provideRouter(routes),
        // http client
        provideHttpClient(withInterceptors([httpclientUrlInterceptor])),
        CookieService,
        // translations
        importProvidersFrom([TranslateModule.forRoot({
            loader: {
                provide: TranslateLoader,
                useFactory: (http: HttpClient) => new TranslateHttpLoader(http, './i18n/', '.json'),
                deps: [HttpClient]
            }
        })])
    ]
};
