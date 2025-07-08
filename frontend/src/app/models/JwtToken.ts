import AuthToken from '@model/AuthToken';

export default class JwtToken {
  constructor(
    public readonly username: string,
    public readonly expiresAt: Date,
    public readonly issuedAt: Date,
    public readonly roles: string[],
    public readonly raw: string
  ) {
  }

  public static fromToken(token: string): JwtToken {
    const data = atob(token.split('.')[1]);
    const json = <{ username: string, exp: number, iat: number, roles: string[] }>JSON.parse(data);
    return new JwtToken(json.username, new Date(json.exp * 1000), new Date(json.iat * 1000), json.roles, token);
  }
}
