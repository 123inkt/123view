import Controller from './Controller.js';

export default class EditRule extends Controller {
    connect() {
        this.role('toggle-help').addEventListener('click', this.toggleHelp.bind(this));
    }

    toggleHelp() {
        const button   = this.role('toggle-help');
        const isActive = button.classList.contains('active');

        button.classList.toggle('active', !isActive);

        for (const element of this.roles('explanation')) {
            element.style.display = isActive ? 'none' : 'block'
        }
    }
}
