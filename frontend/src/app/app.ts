import {Component} from '@angular/core';
import {NavigationEnd, NavigationError, NavigationStart, ResolveEnd, ResolveStart, Router, RouterOutlet} from '@angular/router';
import {Header} from '@component/header/header';
import {Loader} from '@component/loader/loader';
import {TranslateService} from '@ngx-translate/core';
import {Progress} from '@service/progress';

@Component({
    selector: 'app-root',
    imports: [RouterOutlet, Header, Loader],
    templateUrl: './app.html',
    styleUrl: './app.scss'
})
export class App {
    constructor(translate: TranslateService, router: Router, progress: Progress) {
        // setup translations
        translate.addLangs(['en']);
        translate.setDefaultLang('en');
        translate.use('en');
        // setup page load indicator
        router.events.subscribe(event => {
            if (event instanceof ResolveStart || event instanceof NavigationStart) {
                progress.setLoading(true);
            }
            if (event instanceof ResolveEnd || event instanceof NavigationEnd || event instanceof NavigationError) {
                progress.setLoading(false);
            }
        });
    }
}
