import Controller from './Controller.js';

export default class Comment extends Controller {

    connect() {
       this.role('add-comment-textarea').focus();
    }
}
