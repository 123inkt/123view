import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    public connect(): void {
        switch(this.element.tagName.toLowerCase()) {
            case 'select':
                this.element.addEventListener('change', this.submit.bind(this));
                break;
            case 'a':
                this.element.addEventListener('click', this.submit.bind(this));
                break;
            default:
                throw new Error('Unsupported element for FormSubmitter: ' + this.element.tagName);
        }
    }

    private submit(event: Event): void {
        (<HTMLElement>event.target).closest<HTMLFormElement>('form')?.submit();
    }
}
