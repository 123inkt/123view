import {Controller} from '@hotwired/stimulus';
import Elements from '../lib/Elements';

export default class extends Controller<HTMLElement> {
    public toggle(event: Event): void {
        event.preventDefault();

        const target    = event.currentTarget as HTMLElement;
        const accordion = Elements.closestRole(target, 'accordion-item');
        const collapse  = accordion.querySelector('[data-role~=accordion-collapse]') as HTMLElement;

        if (target.classList.contains('collapsed')) {
            target.classList.remove('collapsed');
            collapse.classList.add('show');
        } else {
            target.classList.add('collapsed');
            collapse.classList.remove('show');
        }
    }
}
