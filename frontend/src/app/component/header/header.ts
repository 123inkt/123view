import {Component, Inject} from '@angular/core';
import {AuthenticationService} from '../../service/authentication-service';

@Component({
  selector: 'app-header',
  imports: [],
  templateUrl: './header.html',
  styleUrl: './header.scss'
})
export class Header {
  constructor(@Inject(AuthenticationService) private readonly authenticationService: AuthenticationService) {
  }

  public isLoggedIn() {
    return this.authenticationService.isAuthenticated();
  }

  public isAdmin() {
    return this.authenticationService.isAdmin();
  }
}
