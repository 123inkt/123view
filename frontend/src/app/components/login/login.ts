import {Component, Inject, OnInit} from '@angular/core';
import {FormGroup, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute, Params, Router} from '@angular/router';
import {FormLabel} from '@component/form/form-label/form-label';
import {FormWidget} from '@component/form/form-widget/form-widget';
import {environment} from '@environment/environment';
import LoginViewModel from '@model/viewmodels/LoginViewModel';
import {TranslatePipe} from '@ngx-translate/core';
import {AuthenticationService} from '@service/auth/authentication-service';
import {FormGroupService} from '@service/form-group-service';

@Component({
    selector: 'app-login',
    imports: [ReactiveFormsModule, FormWidget, FormLabel, TranslatePipe],
    templateUrl: './login.html',
    styleUrl: './login.scss'
})
export class Login implements OnInit {
    public declare loginViewModel: LoginViewModel;
    public declare loginForm: FormGroup;
    public environment                 = environment;
    public processing                  = false;
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
        this.loginViewModel = this.route.snapshot.data['loginViewModel'];
        this.loginForm      = this.formGroupService.createFormGroup(this.loginViewModel.form);
    }

    public handleSubmit(): void {
        this.processing   = true;
        this.errorMessage = null;
        this.loginForm.disable();
        this.authService.login(this.loginForm.value as {username: string, password: string})
            .subscribe({
                next: () => this.router.navigate([this.queryParams['returnUrl'] ?? '/']),
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
