import {DATE_PIPE_DEFAULT_OPTIONS} from '@angular/common';
import {HTTP_INTERCEPTORS, HttpClient, provideHttpClient, withInterceptorsFromDi} from '@angular/common/http';
import {ApplicationConfig, importProvidersFrom, provideBrowserGlobalErrorListeners, provideZoneChangeDetection} from '@angular/core';
import {provideRouter, withComponentInputBinding} from '@angular/router';
import BackendApiHttpInterceptor from '@interceptor/backend-api-http-interceptor';
import {TranslateLoader, TranslateModule} from '@ngx-translate/core';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {CookieService} from 'ngx-cookie-service';
import {routes} from './app.routes';

export const appConfig: ApplicationConfig = {
    providers: [
        // configure date
        {provide: DATE_PIPE_DEFAULT_OPTIONS, useValue: {dateFormat: 'dd-MM-yyyy HH:mm'}},
        provideBrowserGlobalErrorListeners(),
        provideZoneChangeDetection({eventCoalescing: true}),
        // routing
        provideRouter(routes, withComponentInputBinding()),
        // http client
        provideHttpClient(withInterceptorsFromDi()),
        {provide: HTTP_INTERCEPTORS, useClass: BackendApiHttpInterceptor, multi: true},
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
