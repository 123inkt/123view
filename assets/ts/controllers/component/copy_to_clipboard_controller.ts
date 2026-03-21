import {Controller} from '@hotwired/stimulus';
import DataSet from '../../lib/DataSet';

/**
 * Create a button that copies content to clipboard when clicked.
 * The button and an optional icon inside the button can change their class for 2 seconds after the copy action.
 *
 * Requires:
 * - data-content="content-to-copy" on the button
 * - data-class-after-copy="class-to-set-after-copy" on the button
 * - optionally, an icon inside the button with:
 *   - data-role="icon"
 *   - data-class-after-copy="class-to-set-after-copy-on-icon"
 *
 * Example
 * <code>
 *   <button class="btn btn-outline-primary"
 *           data-class-after-copy="btn btn-success"
 *           {{ stimulus_controller('component--copy-to-clipboard') }}
 *           {{ stimulus_action('component--copy-to-clipboard', 'onClick', 'click') }}
 *           data-content="clipboard-content-to-copy">
 *       <i class="bi bi-clipboard-check-fill" data-class-after-copy="bi bi-check-lg" data-role="icon"></i>
 *   </button>
 * </code>
 */
export default class extends Controller<HTMLButtonElement> {
    public onClick(event: Event): void {
        event.preventDefault();

        const elementClassBefore = this.element.getAttribute('class') ?? '';
        this.element.className = DataSet.string(this.element, 'classAfterCopy');

        const icon = this.element.querySelector<HTMLElement>('[data-role="icon"]');
        let iconClassBefore = null;
        if (icon !== null) {
            iconClassBefore = icon.getAttribute('class') ?? '';
            icon.className = DataSet.string(icon, 'classAfterCopy');
        }

        void window.navigator.clipboard.writeText(this.element.dataset.content ?? '');

        window.setTimeout(() => {
            this.element.className = elementClassBefore;
            if (icon !== null && iconClassBefore !== null) {
                icon.className = iconClassBefore;
            }
        }, 2000);
    }
}
