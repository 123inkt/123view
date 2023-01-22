import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['panelLeft', 'panelRight'];
    private readonly declare panelLeftTarget: HTMLElement;
    private readonly declare panelRightTarget: HTMLElement;

    public connect(): void {
        this.bind(this.panelLeftTarget, this.panelRightTarget);
        this.bind(this.panelRightTarget, this.panelLeftTarget);
    }

    private bind(source: HTMLElement, target: HTMLElement): void {
        source.addEventListener('scroll', () => {
            target.scrollTop  = source.scrollTop;
            target.scrollLeft = source.scrollLeft;
        });
    }
}
