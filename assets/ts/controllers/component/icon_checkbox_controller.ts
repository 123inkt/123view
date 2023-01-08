import {Controller} from '@hotwired/stimulus';
import Events from '../../lib/Events';

export default class extends Controller {
    public static targets = ['checkbox'];
    private declare checkboxTarget: HTMLInputElement;

    public onClick(event: Event): void {
        Events.stop(event);
        this.checkboxTarget.checked = this.checkboxTarget.checked === false;
        this.checkboxTarget.dispatchEvent(new Event('change'));
    }
}
