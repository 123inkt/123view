import {Injectable} from '@angular/core';
import AuthToken from '@model/AuthToken';
import JwtToken from '@model/JwtToken';
import {BehaviorSubject} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TokenStore {
  public readonly isLoggedIn$;
  private readonly isLoggedInSubject;
  private jwtToken: JwtToken | null = null;

  constructor() {
    this.isLoggedInSubject = new BehaviorSubject<boolean>(false);
    this.isLoggedIn$       = this.isLoggedInSubject.asObservable();
  }

  public setToken(token: AuthToken | null): void {
    this.jwtToken = token === null ? null : JwtToken.fromToken(token);
    this.isLoggedInSubject.next(token !== null);
  }

  public isFullyAuthenticated(): boolean {
    return this.isLoggedInSubject.getValue();
  }

  public isAdmin(): boolean {
    return this.jwtToken !== null && this.jwtToken.roles.includes('ROLE_ADMIN');
  }
}
