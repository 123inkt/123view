import {Component, Inject} from '@angular/core';
import {FormGroup, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {Params} from '@angular/router';
import {FormLabel} from '@component/form/form-label/form-label';
import {FormWidget} from '@component/form/form-widget/form-widget';
import {environment} from '@environment/environment';
import LoginViewModel from '@model/viewmodels/LoginViewModel';
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
  public declare loginForm: FormGroup;
  public environment                 = environment;
  public processing: boolean         = false;
  public errorMessage: string | null = null;
  private queryParams: Params        = {};

  constructor(
    @Inject(AuthenticationService) private readonly authService: AuthenticationService,
    @Inject(FormGroupService) private readonly formGroupService: FormGroupService,
    @Inject(Router) private readonly router: Router,
    private readonly route: ActivatedRoute
  ) {
    this.route.queryParams.subscribe(params => this.queryParams = params);
  }

  public ngOnInit(): void {
    this.loginViewModel = this.route.snapshot.data['resolvedData'];
    this.loginForm      = this.formGroupService.createFormGroup(this.loginViewModel.form);
  }

  public handleSubmit(): void {
    this.processing   = true;
    this.errorMessage = null;
    this.loginForm.disable();
    this.authService.login(this.loginForm.value)
      .subscribe({
        next: () => {
          this.processing = false;
          this.loginForm.enable();
          this.router.navigate([this.queryParams['returnUrl'] ?? '/']);
        },
        error: (error) => {
          this.processing = false;
          this.loginForm.enable();
          this.errorMessage = error.error?.message ?? null;
        }
      });
  }

  public loginWithAzureAd(): void {
    this.authService.azureAdRedirect(this.queryParams);
  }
}
