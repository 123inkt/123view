import {Component} from '@angular/core';
import {RouterLink, RouterOutlet} from '@angular/router';

@Component({
  selector: 'app-root',
  imports: [RouterLink, RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  protected title = '123view';
}
