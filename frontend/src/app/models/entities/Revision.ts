export default interface Revision {
    id: number;
    commitHash: string;
    title: string;
    description: string;
    firstBranch: string;
    authorEmail: string;
    authorName: string;
    createTimestamp: number;
}
