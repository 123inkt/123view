import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Function from '../lib/Function';

export default class extends Controller<HTMLElement> {
    public static values = {file: String};
    private readonly declare fileValue: string;

    public viewFile(event: Event): void {
        event.preventDefault();
        event.stopPropagation();

        this.element.innerHTML = '';
        this.element.classList.add('comment__markdown');
        axios.get(this.fileValue)
            .then(response => this.element.innerHTML = (response.data as string))
            .catch(Function.empty);
    }
}
