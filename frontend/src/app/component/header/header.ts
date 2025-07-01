import {AsyncPipe} from '@angular/common';
import {Component, Inject} from '@angular/core';
import {RouterLink} from '@angular/router';
import {AuthenticationService} from '../../service/authentication-service';

@Component({
  selector: 'app-header',
  imports: [RouterLink, AsyncPipe],
  templateUrl: './header.html',
  styleUrl: './header.scss'
})
export class Header {
  constructor(@Inject(AuthenticationService) public readonly authService: AuthenticationService) {
  }
}
