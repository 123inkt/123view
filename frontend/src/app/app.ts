import {Component} from '@angular/core';
import {NavigationEnd, NavigationStart, ResolveEnd, ResolveStart, Router, RouterOutlet} from '@angular/router';
import {Header} from '@component/header/header';
import {Loader} from '@component/loader/loader';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, Header, Loader],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  public loading: boolean = false;

  constructor(private router: Router) {
    this.router.events.subscribe(event => {
      if (event instanceof ResolveStart || event instanceof NavigationStart) {
        this.loading = true;
      }
      if (event instanceof ResolveEnd || event instanceof NavigationEnd) {
        this.loading = false;
      }
    });
  }
}
