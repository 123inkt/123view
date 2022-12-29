import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['explanation', 'button'];
    declare buttonTarget: HTMLElement;
    declare explanationTargets: HTMLElement[];

    public toggleHelp(): void {
        const isActive = this.buttonTarget.classList.contains('active');
        this.buttonTarget.classList.toggle('active', !isActive);
        this.explanationTargets.forEach(el => el.style.display = isActive ? 'none' : 'block');
    }
}
