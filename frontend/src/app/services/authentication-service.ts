import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {BehaviorSubject, catchError, Observable, pipe, throwError} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {
  public readonly isLoggedIn$;
  private readonly isLoggedInSubject;

  constructor(private readonly httpClient: HttpClient) {
    this.isLoggedInSubject = new BehaviorSubject<boolean>(false);
    this.isLoggedIn$       = this.isLoggedInSubject.asObservable();
  }

  public login(data: {[key: string]: unknown}): Observable<unknown> {
    console.log('logging in with data:', data);
    return this.httpClient.post('api/login', data);
  }

  public logout(): void {
    this.isLoggedInSubject.next(false);
  }

  public isAuthenticated(): boolean {
    return this.isLoggedInSubject.getValue();
  }

  public isAdmin(): boolean {
    return false;
  }
}
