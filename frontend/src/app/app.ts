import {Component} from '@angular/core';
import {NavigationEnd, NavigationError, NavigationStart, Router, RouterOutlet} from '@angular/router';
import {Header} from '@component/header/header';
import {Loader} from '@component/loader/loader';
import {ToastList} from '@component/toast/toast-list/toast-list';
import {TranslateService} from '@ngx-translate/core';
import {Progress} from '@service/progress';

@Component({
    selector: 'app-root',
    imports: [RouterOutlet, Header, Loader, ToastList],
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
            if (event instanceof NavigationStart) {
                progress.setLoading(true);
            }
            if (event instanceof NavigationEnd) {
                progress.setLoading(false);
            }
            if (event instanceof NavigationError) {
                progress.setLoading(false);
            }
        });
    }
}
