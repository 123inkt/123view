import FormView from './FormView';

export default class LoginViewModel {
  public declare form: FormView<
    {
      _username: FormView,
      _password: FormView,
      loginBtn: FormView,
      _csrf_token: FormView
    }
  >;
  public declare azureAdUrl: string;
}
