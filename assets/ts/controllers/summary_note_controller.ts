import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Function from '../lib/Function';

interface SummaryNote {
    status: 'success' | 'warning' | 'progress' | 'unknown';
    title: string;
    html: string;
    url: string;
}

const statusClasses: Record<SummaryNote['status'], string[]> = {
    success: ['btn-outline-success'],
    warning: ['btn-outline-primary', 'dr-btn-orange'],
    progress: ['btn-outline-secondary'],
    unknown: ['btn-outline-secondary'],
};

const statusIcons: Record<SummaryNote['status'], string> = {
    success: 'bi bi-check-circle',
    warning: 'bi bi-exclamation-triangle',
    progress: 'bi bi-gear summary-note-spin',
    unknown: 'bi bi-chat-square-text',
};

export default class extends Controller {
    public static targets = ['link', 'icon', 'panel'];
    public static values  = {id: Number};

    private readonly declare linkTarget: HTMLAnchorElement;
    private readonly declare iconTarget: HTMLElement;
    private readonly declare panelTarget: HTMLElement;
    private readonly declare idValue: number;

    public connect(): void {
        axios.get(`/app/reviews/${this.idValue}/summary-note`)
            .then((response) => {
                if (response.status === 204) {
                    return;
                }

                const summaryNote = response.data as SummaryNote;

                this.element.classList.remove('d-none');
                this.linkTarget.classList.add(...statusClasses[summaryNote.status]);
                this.iconTarget.className = statusIcons[summaryNote.status];
                this.linkTarget.setAttribute('href', summaryNote.url);
                this.linkTarget.setAttribute('title', summaryNote.title);
                this.linkTarget.setAttribute('aria-label', summaryNote.title);
                this.panelTarget.innerHTML = summaryNote.html;
                this.panelTarget.querySelectorAll('a').forEach((link) => {
                    link.target = '_blank';
                    link.rel    = 'noopener noreferrer';
                });
            })
            .catch(Function.empty);
    }

    public position(): void {
        const margin   = 10;
        const left     = this.element.getBoundingClientRect().left;
        const overflow = left + this.panelTarget.offsetWidth + margin - document.documentElement.clientWidth;

        this.panelTarget.style.left = `${-Math.max(0, Math.min(overflow, left - margin))}px`;
    }
}
