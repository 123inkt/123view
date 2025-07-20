import {AsyncPipe} from '@angular/common';
import {Component} from '@angular/core';
import {Progress} from '@service/progress';

@Component({
    selector: 'app-loader',
    imports: [
        AsyncPipe
    ],
    templateUrl: './loader.html',
    styleUrl: './loader.scss'
})
export class Loader {
    constructor(public readonly progress: Progress) {
    }
}
