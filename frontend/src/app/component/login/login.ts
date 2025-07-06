import {Component, Inject} from '@angular/core';
import {FormGroup, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {FormLabel} from '@component/form/form-label/form-label';
import {FormWidget} from '@component/form/form-widget/form-widget';
import LoginViewModel from '@model/LoginViewModel';
import {AuthenticationService} from '@service/authentication-service';
import {FormGroupService} from '@service/form-group-service';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, FormWidget, FormLabel],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login {
  public declare loginViewModel: LoginViewModel;
  public declare profileForm: FormGroup;

  constructor(
    @Inject(AuthenticationService) private readonly authService: AuthenticationService,
    @Inject(FormGroupService) private readonly formGroupService: FormGroupService,
    private readonly route: ActivatedRoute
  ) {
  }

  public ngOnInit(): void {
    this.loginViewModel = this.route.snapshot.data['resolvedData'];
    this.profileForm = this.formGroupService.createFormGroup(this.loginViewModel.form);
  }

  public handleSubmit(): void {
    console.log(this.profileForm.value);
    //this.authService.login('foo', 'bar');
  }
}
