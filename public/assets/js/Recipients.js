import Controller from './Controller.js';

export default class Recipients extends Controller {
    recipientCount = 0;

    connect() {
        this.recipientCount = parseInt(this.el.dataset.recipientCount);
        this.listen('click', 'addRecipient', this.addRecipient.bind(this));
        this.listen('click', 'deleteRecipient', this.deleteRecipient.bind(this));
    }

    addRecipient() {
        const list = this.role('recipient-list');
        if (list.children.length >= 10) {
            return;
        }

        // get the template
        const template = this.role('recipient-template').innerHTML;

        // create new element from template
        const element = this.createElement(template.replace(/__name__/g, String(this.recipientCount++)));

        // add to recipient list
        list.appendChild(element);
    }

    deleteRecipient(element) {
        element.closest('[data-role="recipient"]').remove();
    }
}
