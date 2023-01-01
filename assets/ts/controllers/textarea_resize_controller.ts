import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLTextAreaElement> {

    public connect(): void {
        this.element.addEventListener('input', event => this.commentResizeListener(<HTMLTextAreaElement>event.target));
        this.commentResizeListener(this.element);
    }

    private commentResizeListener(target: HTMLTextAreaElement): void {
        target.style.height = '5px';
        target.style.height = Math.max(84, (target.scrollHeight)) + 'px';
    }
}
