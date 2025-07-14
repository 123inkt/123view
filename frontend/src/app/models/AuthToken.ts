export default interface AuthToken {
    token: string;
    refreshToken: string;
    refreshTokenExpiresAt: number;
}
