import {Injectable} from '@angular/core';
import {ActivatedRoute, Params, Router} from '@angular/router';

@Injectable({providedIn: 'root'})
export default class NavigatorService {
    constructor(private readonly router: Router, private readonly route: ActivatedRoute) {
    }

    public navigateToQuery(queryParams: Params | null): void {
        this.router.navigate([], {relativeTo: this.route, queryParams});
    }

    public combineWithQuery(queryParams: Params | null): void {
        this.router.navigate([], {relativeTo: this.route, queryParams, queryParamsHandling: 'merge'});
    }
}
