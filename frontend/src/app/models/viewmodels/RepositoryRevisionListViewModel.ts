import Repository from '@model/entities/Repository';
import Revision from '@model/entities/Revision';
import PaginatorViewModel from '@model/viewmodels/PaginatorViewModel';

export default class RepositoryRevisionListViewModel {
    public declare repository: Repository;
    public declare revisions: Revision[];
    public declare reviewIds: Record<number, number>;
    public declare paginator: PaginatorViewModel;
}
