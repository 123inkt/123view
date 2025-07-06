import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Params} from '@angular/router';
import {UrlService} from '@service/url-service';
import {BehaviorSubject, Observable} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {
  public readonly isLoggedIn$;
  private readonly isLoggedInSubject;

  constructor(private readonly httpClient: HttpClient, private readonly urlService: UrlService) {
    this.isLoggedInSubject = new BehaviorSubject<boolean>(false);
    this.isLoggedIn$       = this.isLoggedInSubject.asObservable();
  }

  public login(data: { [key: string]: unknown }): Observable<unknown> {
    return this.httpClient.post('api/login', data);
  }

  public azureAdRedirect(searchParams: Params): void {
    this.httpClient.get<{url: string}>(this.urlService.createUrl('/single-sign-on/azure-ad', searchParams))
      .subscribe((result) => {
        window.location.href = result.url;
      });
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
