export default class ElementFactory {
    public static createElement(html: string): HTMLElement {
        html = html.trim();
        if (html.charAt(0) !== '<') {
            return document.createElement(html);
        }

        const container     = document.createElement('div');
        container.innerHTML = html;

        return <HTMLElement>container.firstElementChild;
    }
}
