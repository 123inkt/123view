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
                return el as HTMLElement;
            }
        }

        for (let el = element.previousElementSibling; el !== null; el = el.previousElementSibling) {
            if (el.matches(`[data-role~="${dataRole}"]`)) {
                return el as HTMLElement;
            }
        }

        throw new Error(`No sibling with role '${dataRole}'`);
    }

    public static getScrollParent(element: HTMLElement): HTMLElement | null {
        for (let el = element.parentElement; el !== null; el = el.parentElement) {
            const styles = getComputedStyle(el);
            if (styles.overflow === 'auto' || styles.overflow === 'scroll' || styles.overflowY === 'auto' || styles.overflowY === 'scroll') {
                return el;
            }
        }

        return null;
    }

    public static create(html: string): HTMLElement {
        const trimmedHtml = html.trim();
        if (trimmedHtml.startsWith('<') === false) {
            return document.createElement(trimmedHtml);
        }

        const container     = document.createElement('div');
        container.innerHTML = trimmedHtml;

        return container.firstElementChild as HTMLElement;
    }
}
