const assert = require('node:assert/strict');
const {spawnSync} = require('node:child_process');
const {mkdtempSync, rmSync, writeFileSync} = require('node:fs');
const {tmpdir} = require('node:os');
const {join, resolve} = require('node:path');
const {test} = require('node:test');
const {pathToFileURL} = require('node:url');
const sass = require('sass');

const root = resolve(__dirname, '../..');
const chrome = process.env.CHROME_BIN ?? (process.platform === 'win32'
    ? join(process.env.PROGRAMFILES ?? 'C:/Program Files', 'Google/Chrome/Application/chrome.exe')
    : process.platform === 'darwin' ? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' : 'google-chrome');

function thread(id, collapsed = true) {
    return `<div id="${id}" class="comment__comment-thread comment__state_resolved${collapsed ? ' comment__collapsed' : ''}">
        <div class="comment__comment"><div class="comment__message">Resolved comment</div></div>
        <div class="comment__reply">Reply</div>
        <div class="comment__toolbar">Reply and reactions</div>
    </div>`;
}

function line(id, hidden, collapsed = true) {
    // Match the line/comment-group structure shared by both diff templates.
    return `<div id="${id}-line" class="diff-file__diff-line${hidden ? ' diff-file__diff-line-hidden' : ''}" data-role="diff-line">Source line</div>
        <div class="diff-file__line-comments" data-role="add-comment-inserter">${thread(id, collapsed)}${thread(`${id}-second`, collapsed)}</div>`;
}

function layout(id) {
    return `<div class="${id === 'split' ? 'diff-side-by-side' : 'diff-file__file-diff'}">
        <div class="${id === 'split' ? 'diff-side-by-side__diff-block' : 'diff-file__diff-block'}">
            ${line(`${id}-hidden-first`, true)}
            <div class="diff-file__block-spacer">39 lines hidden</div>
            ${line(`${id}-visible`, false)}
            ${line(`${id}-expanded`, false, false)}
            ${line(`${id}-hidden-last`, true)}
            <div class="diff-file__block-spacer">318 lines hidden</div>
        </div>
    </div>`;
}

test('resolved comment bubbles follow line visibility in both diff layouts', () => {
    const css = ['comment.scss', 'review-file-diff.scss', 'review-file-diff-side-by-side.scss']
        .map(file => sass.compile(join(root, 'assets/styles', file)).css).join('\n');
    const directory = mkdtempSync(join(tmpdir(), '123view-comment-collapse-'));
    try {
        const htmlPath = join(directory, 'comments.html');
        writeFileSync(htmlPath, `<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head><body>
            ${layout('unified')}${layout('split')}${thread('detached')}
            <pre id="results"></pre><script>
                const results = [];
                function check(id, bubble, phase) {
                    const element = document.getElementById(id);
                    const icon = getComputedStyle(element, '::before').content;
                    const actual = {
                        position: getComputedStyle(element).position,
                        commentVisible: getComputedStyle(element.querySelector('.comment__comment')).display !== 'none',
                        replyVisible: getComputedStyle(element.querySelector('.comment__reply')).display !== 'none',
                        toolbarVisible: getComputedStyle(element.querySelector('.comment__toolbar')).display !== 'none',
                        iconVisible: icon !== 'none' && icon !== 'normal' && icon !== '""'
                    };
                    const expected = {position: bubble ? 'absolute' : 'static', commentVisible: !bubble, replyVisible: !bubble, toolbarVisible: !bubble, iconVisible: bubble};
                    results.push({name: phase + ': ' + id, actual, expected});
                }
                for (const layout of ['unified', 'split']) {
                    for (const state of ['hidden-first', 'visible', 'expanded', 'hidden-last']) {
                        for (const suffix of ['', '-second']) {
                            check(layout + '-' + state + suffix, state === 'visible', 'initial');
                        }
                    }
                    const line = document.getElementById(layout + '-hidden-first-line');
                    line.classList.remove('diff-file__diff-line-hidden');
                    check(layout + '-hidden-first', true, 'expand lines');
                    check(layout + '-hidden-first-second', true, 'expand lines');
                    line.classList.add('diff-file__diff-line-hidden');
                    check(layout + '-hidden-first', false, 'hide lines');
                    const comment = document.getElementById(layout + '-hidden-first');
                    comment.replaceWith(comment.cloneNode(true));
                    check(layout + '-hidden-first', false, 'replace thread');
                    const form = document.createElement('form');
                    line.nextElementSibling.append(form);
                    const addedComment = document.getElementById(layout + '-hidden-first').cloneNode(true);
                    addedComment.id = layout + '-added';
                    form.replaceWith(addedComment);
                    check(addedComment.id, false, 'add comment on hidden line');
                    line.classList.remove('diff-file__diff-line-hidden');
                    check(addedComment.id, true, 'expand newly commented line');
                    document.getElementById(layout + '-visible').classList.remove('comment__collapsed');
                    check(layout + '-visible', false, 'expand comment');
                }
                check('detached', true, 'unchanged detached behavior');
                document.getElementById('results').textContent = JSON.stringify(results);
            </script></body></html>`);
        const browser = spawnSync(chrome, [
            '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run', '--no-default-browser-check',
            `--user-data-dir=${join(directory, 'profile')}`, '--dump-dom', pathToFileURL(htmlPath).href
        ], {encoding: 'utf8', timeout: 30000, maxBuffer: 1024 * 1024});
        assert.equal(browser.status, 0, `Cannot run Chrome; set CHROME_BIN to its executable. ${browser.error?.message ?? browser.stderr}`);
        const match = browser.stdout.match(/<pre id="results">([^<]+)<\/pre>/);
        assert.ok(match, 'Browser did not run the regression checks');
        const results = JSON.parse(match[1].replaceAll('&quot;', '"').replaceAll('&amp;', '&'));
        assert.equal(results.length, 31, 'All browser scenarios must run');
        for (const result of results) {
            assert.deepEqual(result.actual, result.expected, result.name);
        }
    } finally {
        rmSync(directory, {recursive: true, force: true, maxRetries: 5, retryDelay: 100});
    }
});
