export default class Comment {
    public declare id: number;
    public declare filePath: string;
    public declare lineReference: string[];
    public declare state: string;
    public declare message: string;
    public declare tag: string | null;
    public declare createTimestamp: number;
    public declare updateTimestamp: number;
}
