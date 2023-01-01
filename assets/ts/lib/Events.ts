export default class Events {
    public static stop(event: Event): void {
        event.preventDefault();
        event.stopPropagation();
    }
}
