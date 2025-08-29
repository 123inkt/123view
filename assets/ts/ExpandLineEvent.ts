import type {Controller} from '@hotwired/stimulus';

export default class ExpandLineEvent extends CustomEvent<unknown> {
    constructor(public readonly lineNumber: string, public readonly direction: 'up' | 'down', public readonly source: Controller<HTMLElement>) {
        super('line-expander');
    }
}
