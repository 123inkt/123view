import {Controller} from 'https://unpkg.com/@hotwired/stimulus/dist/stimulus.js'

export default class EditRule extends Controller {
    static targets = ['recipients', 'recipientTemplate']
    static values  = {recipientCount: Number}

    addRecipient() {
        // gather recipient container and template
        const template = this.recipientTemplateTarget.innerHTML;

        // create new element from template
        const element     = document.createElement('div');
        element.innerHTML = template.replace(/__name__/g, String(this.recipientCountValue++));

        // add to recipient list
        this.recipientsTarget.appendChild(element.firstElementChild);
    }

    deleteRecipient(event) {
        event.target.closest('[data-role=recipient]').remove();
    }
}
