import {Component, Inject} from '@angular/core';
import {AuthenticationService} from '../../service/authentication-service';

@Component({
  selector: 'app-login',
  imports: [],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login {
  constructor(@Inject(AuthenticationService) private readonly authService: AuthenticationService) {
  }

  public onLogin(): void {
    this.authService.login('foo', 'bar');
  }
}
