import FormView from '@model/FormView';

export default class LoginViewModel {
    public declare form: FormView<
        {
            username: FormView,
            password: FormView,
            loginBtn: FormView
        }
    >;
    public declare azureAdUrl: string;
}
