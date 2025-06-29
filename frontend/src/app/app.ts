import {Component} from '@angular/core';
import {RouterLink, RouterOutlet} from '@angular/router';
import {Header} from './component/header/header';

@Component({
  selector: 'app-root',
  imports: [RouterLink, RouterOutlet, Header],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  protected title = '123view';
}
