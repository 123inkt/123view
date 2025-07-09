import FormView from '@model/FormView';

export default class LoginViewModel {
  public declare form: FormView<
    {
      _username: FormView,
      _password: FormView,
      loginBtn: FormView
    }
  >;
  public declare azureAdUrl: string;
}
