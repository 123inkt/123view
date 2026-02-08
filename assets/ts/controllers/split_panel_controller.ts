import {Controller} from '@hotwired/stimulus';
import Draggable from '../lib/Draggable';

export default class extends Controller<HTMLElement> {
    public static targets                            = ['left', 'right', 'divider'];
    private readonly declare leftTarget: HTMLElement;
    private readonly declare rightTarget: HTMLElement;
    private readonly declare dividerTarget: HTMLElement;
    private initialLeftCellWidth: number | undefined = undefined;
    private drag?: Draggable                         = undefined;

    public connect(): void {
        this.drag = new Draggable(this.dividerTarget, {
            onDragStart: this.onDragStart.bind(this),
            onDragMove: this.onDragMove.bind(this),
            onDragStop: this.onDragStop.bind(this)
        }).attach();
    }

    public disconnect(): void {
        this.drag?.detach();
        this.drag = undefined;
    }

    private onDragStart(): void {
        this.initialLeftCellWidth = this.leftTarget.clientWidth;
    }

    private onDragMove(event: {dx: number; dy: number}): void {
        const newLeftWidth = (this.initialLeftCellWidth ?? 0) + event.dx;
        // Prevent the left cell from being resized to less than 200px
        if (newLeftWidth < 200) {
            return;
        }

        const leftWidth                        = this.leftTarget.clientWidth;
        this.element.style.gridTemplateColumns = `${newLeftWidth}px 10px auto`;

        const newRightWidth = this.rightTarget.clientWidth;
        // Prevent the right cell from being resized to less than 20px
        if (newRightWidth < 200) {
            this.element.style.gridTemplateColumns = `${leftWidth}px 10px auto`;
        }
    }

    private onDragStop(): void {
        this.initialLeftCellWidth = undefined;
    }
}
