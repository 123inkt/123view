import {Injectable} from '@angular/core';
import {BehaviorSubject} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {
  public readonly isLoggedIn$;
  private readonly isLoggedInSubject;

  constructor() {
    this.isLoggedInSubject = new BehaviorSubject<boolean>(false);
    this.isLoggedIn$       = this.isLoggedInSubject.asObservable();
  }

  public login(username: string, password: string): void {
    this.isLoggedInSubject.next(true);
  }

  public logout(): void {
    this.isLoggedInSubject.next(false);
  }

  public isAdmin(): boolean {
    return false;
  }
}
