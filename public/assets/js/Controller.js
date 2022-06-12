export default class Controller {
    /** @var HTMLElement */
    el;

    constructor(el) {
        this.el = el;
    }

    connect() {
    }

    listen(eventName, targetName, callback) {
        this.el.addEventListener(eventName, (event) => {
            const element = event.target.closest('[data-role="' + targetName + '"]');
            if (element !== null) {
                callback(element, event);
            }
        });
    }

    createElement(html) {
        html = html.trim();
        if (html.charAt(0) !== '<') {
            return document.createElement(html);
        }

        const container     = document.createElement('div');
        container.innerHTML = html;
        return container.firstElementChild;
    }

    role(role) {
        const element = this.el.querySelector('[data-role="' + role + '"]');
        if (element === null) {
            throw new Error('Unable to find role: ' + role);
        }
        return element;
    }

    roles(role) {
        return this.el.querySelectorAll('[data-role="' + role + '"]').values();
    }
}
