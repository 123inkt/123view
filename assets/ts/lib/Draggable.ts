import type DragOptions from './DragOptions';
import Point from './Point';

/**
 * How to use:
 *
 * <code>new Draggable(element, {handle: handle, ...}).attach();</code>
 *
 * @see DragOptions for more configuration options
 */
export default class Draggable {
    private readonly options: DragOptions;
    private mouseStart: Point = Point.ZERO;

    constructor(private readonly element: HTMLElement, options?: Partial<DragOptions>) {
        this.options  = {...{handle: element, threshold: 6}, ...options} satisfies DragOptions;
        this.start    = this.start.bind(this);
        this.check    = this.check.bind(this);
        this.cancel   = this.cancel.bind(this);
        this.dragMove = this.dragMove.bind(this);
        this.dragStop = this.dragStop.bind(this);
    }

    public getOptions(): DragOptions {
        return this.options;
    }

    public attach(): this {
        const handles = Array.isArray(this.options.handle) ? this.options.handle : [this.options.handle];
        for (const handle of handles) {
            handle.addEventListener('mousedown', this.start);
            handle.addEventListener('touchstart', this.start);
        }
        return this;
    }

    public detach(): this {
        const handles = Array.isArray(this.options.handle) ? this.options.handle : [this.options.handle];
        for (const handle of handles) {
            handle.removeEventListener('mousedown', this.start);
            handle.removeEventListener('touchstart', this.start);
        }
        return this;
    }

    private start(event: Event): void {
        this.mouseStart = Draggable.pointFromEvent(event);
        if (this.options.onStart !== undefined) {
            this.options.onStart(event);
        }

        document.addEventListener('mousemove', this.check);
        document.addEventListener('touchmove', this.check);
        document.addEventListener('mouseup', this.cancel);
        document.addEventListener('touchend', this.cancel);
    }

    private check(event: Event): void {
        const distance = this.mouseStart.distance(Draggable.pointFromEvent(event));
        // dragged distance is below the threshold distance
        if (distance <= this.options.threshold) {
            return;
        }

        if (this.options.onDragStart !== undefined) {
            this.options.onDragStart(event);
        }

        // remove previous listeners
        document.removeEventListener('mousemove', this.check);
        document.removeEventListener('touchmove', this.check);
        document.removeEventListener('mouseup', this.cancel);
        document.removeEventListener('touchend', this.cancel);

        // add new listeners
        document.addEventListener('mousemove ', this.dragMove);
        document.addEventListener('touchmove', this.dragMove);
        document.addEventListener('mouseup', this.dragStop);
        document.addEventListener('touchend', this.dragStop);
    }

    private cancel(): void {
        if (this.options.onCancel !== undefined) {
            this.options.onCancel();
        }
        this.finishDrag();
    }

    private dragMove(event: Event): void {
        const mouseCurrent = Draggable.pointFromEvent(event);
        // calculate the distance from the starting position
        const dx           = mouseCurrent.x - this.mouseStart.x;
        const dy           = mouseCurrent.y - this.mouseStart.y;

        if (this.options.onDragMove !== undefined) {
            this.options.onDragMove({dx, dy}, this.element);
        }
    }

    private dragStop(event: Event): void {
        if (this.options.onDragStop !== undefined) {
            this.options.onDragStop(event);
        }
        this.finishDrag();
    }

    private finishDrag(): void {
        document.removeEventListener('mousemove', this.check);
        document.removeEventListener('touchmove', this.check);
        document.removeEventListener('mouseup', this.cancel);
        document.removeEventListener('touchend', this.cancel);
        document.removeEventListener('mousemove ', this.dragMove);
        document.removeEventListener('touchmove', this.dragMove);
        document.removeEventListener('mouseup', this.dragStop);
        document.removeEventListener('touchend', this.dragStop);
        if (this.options.onComplete !== undefined) {
            this.options.onComplete();
        }
    }

    private static pointFromEvent(event: Event): Point {
        // sniff on event type instead of `instanceof TouchEvent` as desktop browsers might not have TouchEvent obj
        if (event.type.startsWith('touch')) {
            return new Point((event as TouchEvent).touches[0]?.clientX ?? 0, (event as TouchEvent).touches[0]?.clientY ?? 0);
        }

        return new Point((event as MouseEvent).clientX, (event as MouseEvent).clientY);
    }
}
