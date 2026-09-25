import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    private offsetTop = 0;
    private width     = 0;

    public connect(): void {
        this.offsetTop = this.element.offsetTop;
        this.width     = this.element.clientWidth;
        window.addEventListener('resize', this.layout.bind(this));
        window.addEventListener('scroll', this.layout.bind(this));
        this.layout();
    }

    private layout(): void {
        if (window.scrollY < this.offsetTop) {
            this.element.style.position = '';
            this.element.style.width    = '';
            this.element.style.height   = `${String(window.innerHeight - this.offsetTop + window.scrollY)  }px`;
        } else {
            this.element.style.position = 'fixed';
            this.element.style.width    = `${String(this.width)  }px`;
            this.element.style.height   = `${String(window.innerHeight)  }px`;
        }
    }
}
