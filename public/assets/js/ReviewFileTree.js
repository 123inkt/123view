import Controller from './Controller.js';

export default class Review extends Controller {
    connect() {
        try {
            this.role('active-file').scrollIntoView({block: 'center'});
        } catch (e) {
            // active file might not be present.
        }
    }
}
