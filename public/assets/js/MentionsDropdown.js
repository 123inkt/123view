export default class MentionsDropdown {
    dropdown;
    visible  = false;
    users    = [];
    selected = 0;

    constructor(dropdown) {
        this.dropdown = dropdown;
    }

    setUsers(users) {
        this.users = users;
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

    getSelectedUser() {
        return this.users[this.selected];
    }

    /** @private */
    update() {
        let html = '';
        this.users.forEach((user, index) => {
            const selected = this.selected === index;
            html += `
                <li><a class="dropdown-item ${selected ? 'active' : ''}" href="javascript:;" data-user-id="${user.id}">${user.name}</a></li>
            `;
        });
        this.dropdown.innerHTML = html;
    }
}
