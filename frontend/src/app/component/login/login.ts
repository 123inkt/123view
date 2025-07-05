import {Component, Inject} from '@angular/core';
import {FormControl, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {FormWidget} from '@component/form/form-widget/form-widget';
import LoginViewModel from '@model/LoginViewModel';
import {AuthenticationService} from '@service/authentication-service';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, FormWidget],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login {
  public declare loginViewModel: LoginViewModel;

  constructor(
    @Inject(AuthenticationService) private readonly authService: AuthenticationService,
    private readonly route: ActivatedRoute
  ) {
  }

  public ngOnInit(): void {
    this.loginViewModel = this.route.snapshot.data['resolvedData'];
  }

  public handleSubmit(): void {
    this.authService.login('foo', 'bar');
  }
}
