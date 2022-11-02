export default class MentionsDropdown {
    dropdown;
    visible  = false;
    users    = [];
    selected = 0;

    constructor(dropdown) {
        this.dropdown = dropdown;
    }

    setUsers(users) {
        this.users    = users;
        this.selected = 0;
        this.update();
    }

    show() {
        this.visible                = true;
        this.dropdown.style.display = 'block';
    }

    hide() {
        this.visible                = false;
        this.dropdown.style.display = '';
        this.dropdown.innerHTML     = '';
    }

    isVisible() {
        return this.visible;
    }

    selectNext() {
        this.selected = Math.min(this.selected + 1, this.users.length - 1);
        this.update();
    }

    selectPrev() {
        this.selected = Math.max(this.selected - 1, 0);
        this.update();
    }

    getSelectedUser(element) {
        if (element === undefined) {
            return this.users[this.selected];
        }

        const userId = parseInt(element.dataset.userId);
        for (const user of this.users) {
            if (user.id === userId) {
                return user;
            }
        }
        return undefined;
    }

    addEventListener(eventName, callback) {
        this.dropdown.addEventListener(eventName, callback);
    }

    /** @private */
    update() {
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
