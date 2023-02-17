import {Controller} from '@hotwired/stimulus';
import DataSet from '../../lib/DataSet';

export default class extends Controller<HTMLButtonElement> {
    public onClick(): void {
        this.element.className = DataSet.string(this.element, 'classAfterCopy');

        const icon = this.element.querySelector<HTMLElement>('[data-role="icon"]');
        if (icon !== null) {
            icon.className = DataSet.string(icon, 'classAfterCopy');
        }

        void window.navigator.clipboard.writeText(this.element.dataset.content ?? '');

        window.setTimeout(() => {
            this.element.className = DataSet.string(this.element, 'classBeforeCopy');
            if (icon !== null) {
                icon.className = DataSet.string(icon, 'classBeforeCopy');
            }
        }, 2000);
    }
}
