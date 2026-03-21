import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets      = ['panelLeft', 'panelRight'];
    private readonly declare panelLeftTarget: HTMLElement;
    private readonly declare panelRightTarget: HTMLElement;
    private leftLock           = false;
    private rightLock          = false;
    private leftScrollTimeout  = 0;
    private rightScrollTimeout = 0;

    public connect(): void {
        this.panelLeftTarget.addEventListener('scroll', () => this.onLeftScroll());
        this.panelRightTarget.addEventListener('scroll', () => this.onRightScroll());
    }

    private onLeftScroll(): void {
        if (this.leftLock) {
            return;
        }
        window.clearTimeout(this.leftScrollTimeout);
        this.rightLock = true;
        this.panelRightTarget.scroll({left: this.panelLeftTarget.scrollLeft, top: this.panelLeftTarget.scrollTop});
        this.leftScrollTimeout = window.setTimeout(() => this.rightLock = false, 200);
    }

    private onRightScroll(): void {
        if (this.rightLock) {
            return;
        }
        window.clearTimeout(this.rightScrollTimeout);
        this.leftLock = true;
        this.panelLeftTarget.scroll({left: this.panelRightTarget.scrollLeft, top: this.panelRightTarget.scrollTop});
        this.rightScrollTimeout = window.setTimeout(() => this.leftLock = false, 200);
    }
}
