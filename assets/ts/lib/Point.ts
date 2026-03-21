export default class Point {
    public static readonly ZERO = new Point(0, 0);

    constructor(public readonly x: number, public readonly y: number) {
    }

    /**
     * Calculate the distance in pixels between this point and the given
     */
    public distance(other: Point): number {
        const dx = (this.x - other.x) ** 2;
        const dy = (this.y - other.y) ** 2;
        return Math.round(Math.sqrt(dx + dy));
    }
}
