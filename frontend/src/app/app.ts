import {Component} from '@angular/core';
import {NavigationEnd, NavigationError, NavigationStart, ResolveEnd, ResolveStart, Router, RouterOutlet} from '@angular/router';
import {Header} from '@component/header/header';
import {Loader} from '@component/loader/loader';
import {TranslateService} from '@ngx-translate/core';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, Header, Loader],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  public loading: boolean = false;

  constructor(private readonly translate: TranslateService, private readonly router: Router) {
    // setup translations
    this.translate.addLangs(['en']);
    this.translate.setDefaultLang('en');
    this.translate.use('en');
    // setup page load indicator
    this.router.events.subscribe(event => {
      if (event instanceof ResolveStart || event instanceof NavigationStart) {
        this.loading = true;
      }
      if (event instanceof ResolveEnd || event instanceof NavigationEnd || event instanceof NavigationError) {
        this.loading = false;
      }
    });
  }
}
