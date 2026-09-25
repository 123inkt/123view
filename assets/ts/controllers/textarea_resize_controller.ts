import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLTextAreaElement> {
    public connect(): void {
        this.element.addEventListener('input', event => this.commentResizeListener(event.target as HTMLTextAreaElement));
        this.commentResizeListener(this.element);
    }

    private commentResizeListener(target: HTMLTextAreaElement): void {
        target.style.height = '5px';
        target.style.height = `${String(Math.max(84, (target.scrollHeight)))  }px`;
    }
}
