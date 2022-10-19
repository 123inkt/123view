import Controller from './Controller.js';

export default class Review extends Controller {

    connect() {
        this.listen('click', 'line-add-comment', this.addComment.bind(this));
    }

    addComment(target) {
        // build the url
        const location = new URL(window.location.href);
        location.searchParams.set('filePath', this.role('revision-file').dataset.file);
        location.searchParams.set('addComment', target.dataset.lineBefore + ':' + target.dataset.lineAfter);

        // forward
        window.location = location.toString();
    }
}
