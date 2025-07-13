import Repository from '@model/entities/Repository';

export default class CodeReview {
    public declare id: number;
    public declare projectId: number;
    public declare title: string;
    public declare repository: Repository;
}
