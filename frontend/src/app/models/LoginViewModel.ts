import FormView from './FormView';

export default class LoginViewModel {
  public declare form: FormView<
    {
      _username: FormView,
      _password: FormView,
      loginBtn: FormView,
      _csrfToken: FormView
    }
  >;
  public declare azureAdUrl: string;
}
