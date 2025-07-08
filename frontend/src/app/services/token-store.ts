import {Injectable} from '@angular/core';
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

  public setToken(token: JwtToken | null): void {
    this.jwtToken = token;
    this.isLoggedInSubject.next(token !== null);
  }

  public isFullyAuthenticated(): boolean {
    return this.isLoggedInSubject.getValue();
  }

  public isAdmin(): boolean {
    return this.jwtToken !== null && this.jwtToken.roles.includes('ROLE_ADMIN');
  }
}
