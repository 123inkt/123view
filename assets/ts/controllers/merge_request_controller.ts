import {Controller} from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    public static targets = ['icon'];
    public static values  = {id: Number};

    private readonly declare iconTarget: HTMLElement;
    private readonly declare idValue: number;

    public connect(): void {
        axios.get(`/api/review/${this.idValue}/merge-request`)
            .then((response) => {
                if (response.data === null) {
                    return;
                }
                this.element.classList.remove('d-none');
                this.element.setAttribute('href', String(response.data.url));
                this.element.setAttribute('title', String(response.data.title));
                this.iconTarget.classList.add(String(response.data.icon));
            })
            .catch(() => {
                // do nothing
            });
    }
}
