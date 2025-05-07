import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    public static values = {actionReview: String, actionCode: String};

    private readonly declare actionReviewValue: string;
    private readonly declare actionCodeValue: string;

    public onModeChange(event: Event): void {
        const value = (event.target as HTMLSelectElement).value;
        if (value === 'review') {
            this.element.setAttribute('action', this.actionReviewValue);
        } else if (value === 'code') {
            this.element.setAttribute('action', this.actionCodeValue);
        } else {
            throw new Error('Unknown mode: ' + value);
        }
    }
}
