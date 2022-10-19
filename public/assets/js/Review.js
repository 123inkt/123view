import Controller from './Controller.js';

export default class Review extends Controller {

    connect() {
        this.listen('click', 'line-add-comment', this.addComment.bind(this));
    }

    addComment(target) {
        // get file and line numbers
        const lineBefore = target.dataset.lineBefore;
        const lineAfter  = target.dataset.lineAfter;
        const file       = this.role('revision-file').dataset.file;

        // build the url
        const location = new URL(window.location.href);
        location.searchParams.set('filePath', file);
        location.searchParams.set('addComment', lineBefore + ':' + lineAfter);

        // forward
        window.location = location.toString();
    }
}
