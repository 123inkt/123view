import Repository from '@model/entities/Repository';

export default interface CodeReview {
    id: number;
    projectId: number;
    title: string;
    repository: Repository;
}
