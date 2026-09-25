import {Controller} from '@hotwired/stimulus';
import Draggable from '../lib/Draggable';

export default class extends Controller<HTMLElement> {
    public static targets                            = ['left', 'right', 'divider'];
    private readonly declare leftTarget: HTMLElement;
    private readonly declare dividerTarget: HTMLElement;
    private initialLeftCellWidth: number | undefined = undefined;
    private drag?: Draggable                         = undefined;

    public connect(): void {
        this.drag = new Draggable(this.dividerTarget, {
            onStart: this.onStart.bind(this),
            onDragStart: this.onDragStart.bind(this),
            onDragMove: this.onDragMove.bind(this),
            onDragStop: this.onDragStop.bind(this),
            onComplete: this.onComplete.bind(this),
        }).attach();
    }

    public disconnect(): void {
        this.drag?.detach();
        this.drag = undefined;
    }

    private onStart(): void {
        this.dividerTarget.classList.toggle('dragging', true);
    }

    private onDragStart(): void {
        this.initialLeftCellWidth = this.leftTarget.clientWidth;
    }

    private onDragMove(event: {dx: number; dy: number}): void {
        const containerWidth = this.element.clientWidth;
        const dividerWidth   = this.dividerTarget.clientWidth;

        // calculate new widths based on pixels
        let newLeftWidth  = (this.initialLeftCellWidth ?? 0) + event.dx;
        let newRightWidth = containerWidth - newLeftWidth - dividerWidth;

        // bound width within the container with 100px minimum
        newLeftWidth  = Math.max(100, Math.min(newLeftWidth, containerWidth - dividerWidth - 100));
        newRightWidth = Math.max(100, Math.min(newRightWidth, containerWidth - dividerWidth - 100));

        // calculate new widths based on percentage
        const leftPercentage  = (newLeftWidth / containerWidth * 100).toFixed(10);
        const rightPercentage = (newRightWidth / containerWidth * 100).toFixed(10);

        // format grid template style
        this.element.style.gridTemplateColumns = `${leftPercentage}% ${dividerWidth}px ${rightPercentage}%`;
    }

    private onDragStop(): void {
        this.initialLeftCellWidth = undefined;
    }

    private onComplete(): void {
        this.dividerTarget.classList.toggle('dragging', false);
    }
}
