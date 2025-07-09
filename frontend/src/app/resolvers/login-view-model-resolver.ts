import {Inject, Injectable} from '@angular/core';
import {Resolve} from '@angular/router';
import LoginViewModel from '@model/viewmodels/LoginViewModel';
import {LoginService} from '@service/login-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class LoginViewModelResolver implements Resolve<LoginViewModel> {
    constructor(@Inject(LoginService) private readonly loginService: LoginService) {
    }

    public resolve(): Observable<LoginViewModel> {
        return this.loginService.getLoginForm();
    }
}
