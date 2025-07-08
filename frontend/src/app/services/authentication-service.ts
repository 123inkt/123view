import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Params} from '@angular/router';
import AuthToken from '@model/AuthToken';
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

  public login(data: { [key: string]: unknown }): Observable<AuthToken> {
    return this.httpClient.post<AuthToken>('api/login', data)
      .pipe(
        tap((token) => this.tokenStore.setToken(token)),
        share()
      );
  }

  public logout(): void {
    this.tokenStore.setToken(null);
  }

  public azureAdRedirect(searchParams: Params): void {
    this.httpClient.get<{ url: string }>(this.urlService.createUrl('/single-sign-on/azure-ad', searchParams))
      .subscribe((result) => {
        window.location.href = result.url;
      });
  }
}
