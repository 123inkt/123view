import AuthToken from '@model/AuthToken';

export default class RefreshToken {
    constructor(public readonly token: string, public readonly expiresAt: Date) {
    }

    public static fromAuthToken(token: AuthToken): RefreshToken {
        return new RefreshToken(token.refreshToken, new Date(token.refreshTokenExpiresAt * 1000));
    }

    public static fromCookie(cookie: string): RefreshToken {
        const cookieParts = cookie.split(';');
        return new RefreshToken(cookieParts[0], new Date(parseInt(cookieParts[1])));
    }
}
