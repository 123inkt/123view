import Repository from '@model/entities/Repository';

export default class RepositoriesViewModel {
    public declare repositories: Repository[];
    public declare revisionCount: Record<number, number>;
}
