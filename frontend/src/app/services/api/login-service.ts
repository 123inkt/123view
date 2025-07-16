import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import LoginViewModel from '@model/viewmodels/LoginViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class LoginService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getLoginForm(): Observable<LoginViewModel> {
        const context = new HttpContext()
            .set(HttpClientContext.PublicUrl, true)
            .set(HttpClientContext.BackendApi, true);

        return this.httpClient.get<LoginViewModel>('api/view-model/login', {context});
    }
}
