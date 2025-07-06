import {HttpClient} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import LoginViewModel from '@model/LoginViewModel';
import {Observable} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LoginService {
  constructor(@Inject(HttpClient) private httpClient: HttpClient) {
  }

  public getLoginForm(): Observable<LoginViewModel> {
    return this.httpClient.get<LoginViewModel>('login');
  }
}
