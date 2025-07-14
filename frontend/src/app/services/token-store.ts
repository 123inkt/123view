import {Inject, Injectable} from '@angular/core';
import JwtToken from '@model/JwtToken';
import RefreshToken from '@model/RefreshToken';
import {CookieService} from 'ngx-cookie-service';
import {BehaviorSubject} from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class TokenStore {
    private static readonly TokenCookieName = 'jwtToken';
    private static readonly RefreshTokenCookieName = 'refreshToken';

    public readonly isLoggedIn$;
    private readonly isLoggedInSubject;
    private jwtToken: JwtToken | null         = null;
    private refreshToken: RefreshToken | null = null;

    constructor(@Inject(CookieService) private cookieService: CookieService) {
        this.isLoggedInSubject = new BehaviorSubject<boolean>(false);
        this.isLoggedIn$       = this.isLoggedInSubject.asObservable();
        if (this.cookieService.check(TokenStore.TokenCookieName)) {
            this.jwtToken = JwtToken.fromToken(this.cookieService.get(TokenStore.TokenCookieName));
            this.isLoggedInSubject.next(true);
        }
        if (this.cookieService.check(TokenStore.RefreshTokenCookieName)) {
            this.refreshToken = RefreshToken.fromCookie(this.cookieService.get(TokenStore.RefreshTokenCookieName));
            this.isLoggedInSubject.next(true);
        }
    }

    public setToken(token: JwtToken, refreshToken: RefreshToken): void {
        this.jwtToken     = token;
        this.refreshToken = refreshToken;
        this.isLoggedInSubject.next(true);
        this.cookieService.set(
            TokenStore.TokenCookieName,
            token.raw,
            {secure: true, sameSite: 'Strict', expires: token.expiresAt}
        );
        this.cookieService.set(
            TokenStore.RefreshTokenCookieName,
            refreshToken.token + ';' + refreshToken.expiresAt.valueOf(),
            {secure: true, sameSite: 'Strict', expires: refreshToken.expiresAt}
        );
    }

    public clearToken() {
        this.jwtToken = null;
        this.refreshToken = null;
        this.isLoggedInSubject.next(false);
        this.cookieService.delete(TokenStore.TokenCookieName);
        this.cookieService.delete(TokenStore.RefreshTokenCookieName);
    }

    public getRefreshToken(): RefreshToken | null {
        return this.refreshToken;
    }

    public getUserIdentifier(): string | null {
        return this.jwtToken?.username ?? null;
    }

    public isFullyAuthenticated(): boolean {
        return this.isLoggedInSubject.getValue();
    }

    public willExpire(): boolean {
        if (this.jwtToken === null) {
            return true;
        }

        // will jwt token expire within 5 minutes from now
        return this.jwtToken.expiresAt.valueOf() <= new Date().valueOf() + 300000;
    }

    public isAdmin(): boolean {
        return this.jwtToken !== null && this.jwtToken.roles.includes('ROLE_ADMIN');
    }
}
