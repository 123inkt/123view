import {Injectable} from '@angular/core';
import {BehaviorSubject} from 'rxjs';

@Injectable({providedIn: 'root'})
export class Progress {
    public readonly isLoading$;
    private readonly isLoadingSubject;
    private active = 0;

    constructor() {
        this.isLoadingSubject = new BehaviorSubject<boolean>(false);
        this.isLoading$       = this.isLoadingSubject.asObservable();
    }

    public setLoading(loading: boolean): void {
        if (loading) {
            ++this.active;
        } else {
            --this.active;
        }
        this.isLoadingSubject.next(this.active > 0);
    }
}
