export default class Elements {

    public static closest(element: HTMLElement, selector: string): HTMLElement {
        const target = element.closest<HTMLElement>(selector);
        if (target === null) {
            throw new Error(`No parent with selector '${selector}' exists.`);
        }
        return target;
    }

    public static closestRole(element: HTMLElement, dataRole: string): HTMLElement {
        const target = element.closest<HTMLElement>(`[data-role~="${dataRole}"]`);
        if (target === null) {
            throw new Error(`No parent with role '${dataRole}' exists.`);
        }
        return target;
    }

    public static siblingRole(element: HTMLElement, dataRole: string): HTMLElement {
        for (let el = element.nextElementSibling; el !== null; el = el.nextElementSibling) {
            if (el.matches(`[data-role~="${dataRole}"]`)) {
                return <HTMLElement>el;
            }
        }

        for (let el = element.previousElementSibling; el !== null; el = el.previousElementSibling) {
            if (el.matches(`[data-role~="${dataRole}"]`)) {
                return <HTMLElement>el;
            }
        }

        throw new Error(`No sibling with role '${dataRole}'`);
    }

    public static create(html: string): HTMLElement {
        html = html.trim();
        if (html.charAt(0) !== '<') {
            return document.createElement(html);
        }

        const container     = document.createElement('div');
        container.innerHTML = html;

        return <HTMLElement>container.firstElementChild;
    }
}
