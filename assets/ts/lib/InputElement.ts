export default class InputElement {

    public static insertAtCursor(element: HTMLTextAreaElement, text: string): void {
        element.value =
            element.value.substring(0, element.selectionStart)
            + text
            + element.value.substring(element.selectionEnd, element.value.length);
    }
}
