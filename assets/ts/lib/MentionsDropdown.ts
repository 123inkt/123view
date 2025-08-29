import type User from './User';

export default class MentionsDropdown {
    private visible       = false;
    private users: User[] = [];
    private selected      = 0;

    constructor(private readonly dropdown: HTMLElement) {
    }

    public setUsers(users: User[]): void {
        this.users    = users;
        this.selected = 0;
        this.update();
    }

    public show(): void {
        this.visible                = true;
        this.dropdown.style.display = 'block';
    }

    public hide(): void {
        this.visible                = false;
        this.dropdown.style.display = '';
        this.dropdown.innerHTML     = '';
    }

    public isVisible(): boolean {
        return this.visible;
    }

    public selectNext(): void {
        this.selected = Math.min(this.selected + 1, this.users.length - 1);
        this.update();
    }

    public selectPrev(): void {
        this.selected = Math.max(this.selected - 1, 0);
        this.update();
    }

    public getSelectedUser(element?: HTMLElement): User | undefined {
        if (element === undefined) {
            return this.users[this.selected];
        }

        const userId = parseInt(element.dataset.userId ?? '0');
        for (const user of this.users) {
            if (user.id === userId) {
                return user;
            }
        }
        return undefined;
    }

    public addEventListener(eventName: string, callback: (event: Event) => void): void {
        this.dropdown.addEventListener(eventName, callback);
    }

    private update(): void {
        let html = '';
        this.users.forEach((user, index) => {
            const selected = this.selected === index;
            html += `
                <li><span class="dropdown-item ${selected ? 'active' : ''}" data-user-id="${user.id}">${user.name}</span></li>
            `;
        });
        this.dropdown.innerHTML = html;
    }
}
