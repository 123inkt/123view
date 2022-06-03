import {Controller} from 'https://unpkg.com/@hotwired/stimulus/dist/stimulus.js'

export default class EditRule extends Controller {
    static targets = ['recipients', 'recipient', 'recipientTemplate', ]
    static values  = {recipientCount: Number}

    addRecipient() {
        if (this.recipientsTarget.children.length >= 10) {
            return;
        }

        // gather recipient container and template
        const template = this.recipientTemplateTarget.innerHTML;

        // create new element from template
        const element     = document.createElement('div');
        element.innerHTML = template.replace(/__name__/g, String(this.recipientCountValue++));

        // add to recipient list
        this.recipientsTarget.appendChild(element.firstElementChild);
    }

    deleteRecipient(event) {
        this.recipientTargets.forEach((recipient) => {
            if (recipient.contains(event.target)) {
                recipient.remove();
            }
        });
    }
}
