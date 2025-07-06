import {Injectable} from '@angular/core';
import {Params, Router, UrlSerializer} from '@angular/router';

@Injectable({providedIn: 'root'})
export class UrlService {
  constructor(private readonly router: Router, private readonly urlSerializer: UrlSerializer) {
  }

  public createUrl(url: string, queryParams?: Params): string {
    return this.urlSerializer.serialize(this.router.createUrlTree([url], {queryParams}));
  }
}
