import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {
  constructor() {
  }

  public isAuthenticated(): boolean {
    return false;
  }

  public isAdmin(): boolean {
    return false;
  }
}
