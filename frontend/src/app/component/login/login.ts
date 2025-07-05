import {Component, Inject} from '@angular/core';
import {FormControl, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import LoginViewModel from '@model/LoginViewModel';
import {AuthenticationService} from '@service/authentication-service';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login {
  public loginForm = new FormGroup({
    username: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required])
  });
  public loginViewModel!: LoginViewModel;

  constructor(
    @Inject(AuthenticationService) private readonly authService: AuthenticationService,
    private readonly route: ActivatedRoute
  ) {
  }

  public ngOnInit(): void {
    this.loginViewModel = this.route.snapshot.data['resolvedData'];
    console.log(this.loginViewModel);
  }

  public handleSubmit(): void {
    this.authService.login('foo', 'bar');
  }
}
