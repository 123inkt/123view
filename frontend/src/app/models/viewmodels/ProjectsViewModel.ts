import Repository from '@model/entities/Repository';

export default class ProjectsViewModel {
    public declare repositories: Repository[];
    public declare revisionCount: Record<number, number>;
}
