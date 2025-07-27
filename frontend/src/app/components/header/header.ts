import {AsyncPipe} from '@angular/common';
import {Component} from '@angular/core';
import {Router, RouterLink} from '@angular/router';
import {AuthenticationService} from '@service/auth/authentication-service';
import {TokenStore} from '@service/auth/token-store';

@Component({
    selector: 'app-header',
    imports: [RouterLink, AsyncPipe],
    templateUrl: './header.html',
    styleUrl: './header.scss'
})
export class Header {
    constructor(
        public readonly tokenStore: TokenStore,
        public readonly authService: AuthenticationService,
        public readonly router: Router
    ) {
    }

    public logout(): void {
        this.authService.logout();
        this.router.navigate(['login']);
    }
}
