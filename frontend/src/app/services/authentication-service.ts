import {HttpClient, HttpContext, HttpContextToken} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Params} from '@angular/router';
import AuthToken from '@model/AuthToken';
import JwtToken from '@model/JwtToken';
import RefreshToken from '@model/RefreshToken';
import HttpClientContext from '@service/http-client-context';
import {TokenStore} from '@service/token-store';
import {UrlService} from '@service/url-service';
import {Observable, share, tap} from 'rxjs';

@Injectable({providedIn: 'root'})
export class AuthenticationService {
    constructor(
        private readonly httpClient: HttpClient,
        private readonly urlService: UrlService,
        private readonly tokenStore: TokenStore
    ) {
    }

    public login(data: {username: string, password: string}): Observable<AuthToken> {
        const context = new HttpContext().set(HttpClientContext.PublicUrl, true);

        return this.httpClient.post<AuthToken>('api/login', data, {context: context})
            .pipe(
                tap((token) => this.tokenStore.setToken(JwtToken.fromAuthToken(token), RefreshToken.fromAuthToken(token))),
                share()
            );
    }

    public logout(): void {
        this.tokenStore.clearToken();
    }

    public azureAdRedirect(searchParams: Params): void {
        this.httpClient.get<{url: string}>(this.urlService.createUrl('/single-sign-on/azure-ad', searchParams))
            .subscribe((result) => {
                window.location.href = result.url;
            });
    }
}
