export default interface DragOptions {
    /**
     * The element the will act as handlebar for the draggable element. If absent, the whole element will be draggable.
     * If a string is given, it will be used as a querySelector to find the handle element.
     */
    handle: HTMLElement | HTMLElement[];

    /**
     * The distance in pixels before the drag will be initiated. Default is 6px
     */
    threshold: number;

    /**
     * Event listener invoked when the drag is potentially started (before threshold).
     */
    onStart?: (event: Event) => void;

    /**
     * Event listener invoked when the drag is stopped before reaching the threshold point.
     */
    onCancel?: () => void;

    /**
     * Event listener invoked when the drag events is initiated (after threshold).
     */
    onDragStart?: (event: Event) => void;

    /**
     * Event listener invoked when the element is being dragged
     *
     * @param event the x+y distance dragged from the starting point
     * @param element the original element given to draggable
     */
    onDragMove?: (event: {dx: number; dy: number}, element: HTMLElement) => void;

    /**
     * Event listener invoked when the drag motion stops (after threshold).
     */
    onDragStop?: (event: Event) => void;

    /**
     * Invoked always when the mouse motion has ended, either before or after reaching the drag threshold.
     */
    onComplete?: () => void;
}
