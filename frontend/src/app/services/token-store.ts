import {Inject, Injectable} from '@angular/core';
import JwtToken from '@model/JwtToken';
import {CookieService} from 'ngx-cookie-service';
import {BehaviorSubject} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TokenStore {
  private static readonly TokenCookieName = 'jwtToken';

  public readonly isLoggedIn$;
  private readonly isLoggedInSubject;
  private jwtToken: JwtToken | null = null;

  constructor(@Inject(CookieService) private cookieService: CookieService) {
    this.isLoggedInSubject = new BehaviorSubject<boolean>(false);
    this.isLoggedIn$       = this.isLoggedInSubject.asObservable();
    if (this.cookieService.check(TokenStore.TokenCookieName)) {
      this.setToken(JwtToken.fromToken(this.cookieService.get(TokenStore.TokenCookieName)));
    }
  }

  public setToken(token: JwtToken | null): void {
    this.jwtToken = token;
    this.isLoggedInSubject.next(token !== null);
    if (token !== null) {
      this.cookieService.set(TokenStore.TokenCookieName, token.raw, {secure: true, sameSite: 'Strict', expires: token.expiresAt});
    } else {
      this.cookieService.delete(TokenStore.TokenCookieName);
    }
  }

  public isFullyAuthenticated(): boolean {
    return this.isLoggedInSubject.getValue();
  }

  public isAdmin(): boolean {
    return this.jwtToken !== null && this.jwtToken.roles.includes('ROLE_ADMIN');
  }
}
