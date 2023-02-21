import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    private offsetTop: number = 0;

    public connect(): void {
        this.offsetTop = this.element.offsetTop;
        window.addEventListener('resize', this.layout.bind(this));
        window.addEventListener('scroll', this.layout.bind(this));
        this.layout();
    }

    private layout(): void {
        if (window.scrollY < this.offsetTop) {
            this.element.style.position = '';
            this.element.style.height   = String(window.innerHeight - this.offsetTop + window.scrollY) + 'px';
        } else {
            this.element.style.position = 'sticky';
            this.element.style.height   = String(window.innerHeight) + 'px';
        }
    }
}
