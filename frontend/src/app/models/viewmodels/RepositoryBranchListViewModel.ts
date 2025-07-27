import CodeReview from '@model/entities/CodeReview';
import Repository from '@model/entities/Repository';

export default class RepositoryBranchListViewModel {
    public declare repository: Repository;
    public declare branches: string[];
    public declare mergedBranches: string[];
    public declare reviews: Record<string, CodeReview>; // keyed by branch name
}
