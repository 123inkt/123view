import {Component, input} from '@angular/core';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';

@Component({
  selector: 'app-timeline',
  imports: [],
  templateUrl: './timeline.html',
  styleUrl: './timeline.scss'
})
export class Timeline {
  public viewModel = input.required<TimelineViewModel>();
}
