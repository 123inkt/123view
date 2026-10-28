// Governance-only validation. Supply js-yaml through NODE_PATH; no app dependencies.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const yaml = require('js-yaml');
const root = path.resolve(__dirname, '..');
const repo = path.resolve(root, '../..');
const expected = {
  G0: ['G0a', 'G0b'], G0a: [], G0b: ['G0a'], F1: ['G0'], P1: ['F1'],
  P2: ['P1'], P3: ['P1'], S1: ['P2', 'P3'], R1: ['P2'], R2: ['R1'],
  R3: ['R2'], R4: ['R3'], R5: ['R3'], R6: ['R5'], R7: ['R6'],
  R8: ['R1'], R9: ['P3', 'R2', 'R8'], P4: ['P3', 'R1'],
  R10: ['R4', 'R5', 'R6', 'R7', 'R8', 'R9'], U1: ['F1'], U2: ['F1'],
  N1: ['F1'], N2: ['N1', 'R3'], A1: ['F1'], A2: ['F1'], A3: ['F1'],
  X1: ['F1', 'P1', 'P2', 'P3', 'S1', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'R8', 'R9', 'P4', 'R10', 'U1', 'U2', 'N1', 'N2', 'A1', 'A2', 'A3'],
  C1: ['G0', 'G0a', 'G0b', 'F1', 'P1', 'P2', 'P3', 'S1', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'R8', 'R9', 'P4', 'R10', 'U1', 'U2', 'N1', 'N2', 'A1', 'A2', 'A3', 'X1'],
};
const checkStates = ['passed', 'failed', 'blocked', 'not_run', 'not_applicable'];
const lifecycle = ['proposed', 'ready', 'implementing', 'candidate', 'independently-reviewed', 'CI-passed', 'merged', 'human-review-pending', 'accepted'];
const sha = value => typeof value === 'string' && /^[a-f0-9]{40}$/.test(value);
function validate(data) {
  assert.equal(data.schema_version, 1);
  assert.equal(data.target_branch, 'angular');
  assert.equal(data.max_active_implementation_workers, 1);
  assert.deepEqual(data.check_status_values, checkStates);
  assert.deepEqual(data.lifecycle_values, lifecycle);
  assert.deepEqual(Object.keys(data.slices).sort(), Object.keys(expected).sort());
  for (const [id, item] of Object.entries(data.slices)) {
    assert.deepEqual(item.depends_on, expected[id], `${id}: approved dependencies`);
    assert.equal(item.pr_target, 'angular');
    assert.ok(lifecycle.includes(item.lifecycle));
    assert.ok(Array.isArray(item.acceptance) && item.acceptance.length > 0);
    assert.ok(item.acceptance.every(x => typeof x === 'string' && x.length > 20));
    for (const field of Object.keys(data.defaults)) assert.ok(field in item, `${id}: ${field}`);
    for (const field of ['base_sha', 'parent_sha', 'candidate_sha', 'merge_sha']) {
      assert.ok(item[field] === null || sha(item[field]), `${id}: ${field}`);
    }
    for (const [name, check] of Object.entries(item.checks)) {
      assert.ok(checkStates.includes(check.status), `${id}: ${name}`);
      if (check.status === 'passed') assert.ok(check.evidence, `${id}: pass needs evidence`);
      if (check.status === 'not_applicable') assert.ok(check.reason, `${id}: scope rationale required`);
    }
    for (const record of [item.ci, item.independent_review]) {
      assert.ok(checkStates.includes(record.status));
      if (record.status === 'passed') {
        assert.ok(sha(item.candidate_sha));
        assert.equal(record.sha, item.candidate_sha, `${id}: stale SHA`);
      }
    }
    if (item.ci.status === 'passed') {
      assert.ok(item.ci.run_url && item.ci.required_gate_verified);
    }
    if (item.independent_review.status === 'passed') {
      assert.ok(item.independent_review.reviewer && item.independent_review.evidence);
    }
    assert.ok(data.human_status_values.includes(item.human_review.status));
    if (item.lifecycle === 'accepted') {
      assert.ok(sha(item.merge_sha));
      assert.equal(item.human_review.status, 'accepted');
      assert.equal(item.human_review.sha, item.merge_sha);
      assert.ok(item.human_review.owner && item.human_review.evidence);
      assert.equal(item.ci.status, 'passed');
      assert.equal(item.independent_review.status, 'passed');
    }
    for (const record of item.evidence) {
      const relative = typeof record === 'string' ? record : record.path;
      assert.ok(fs.existsSync(path.join(root, relative)), `${id}: missing evidence ${relative}`);
    }
  }
  assert.ok(Object.values(data.slices).filter(x => x.lifecycle === 'implementing').length <= 1);
  const done = new Set();
  function visit(id, active = new Set()) {
    assert.ok(!active.has(id), `Dependency cycle at ${id}`);
    if (done.has(id)) return;
    for (const dependency of data.slices[id].depends_on) visit(dependency, new Set([...active, id]));
    done.add(id);
  }
  Object.keys(data.slices).forEach(id => visit(id));
}
const ledger = yaml.load(fs.readFileSync(path.join(root, 'ledger.yaml'), 'utf8'));
validate(ledger);
console.log(`PASS: ${Object.keys(ledger.slices).length} slices; approved dependencies, tracking fields and gate identities`);
const mutations = [
  d => { d.slices.F1.depends_on = ['G0a']; },
  d => { d.slices.G0a.pr_target = 'master'; },
  d => { d.slices.G0a.checks.governance = { status: 'skipped' }; },
  d => { d.slices.G0a.checks.governance = { status: 'not_applicable' }; },
  d => { d.slices.G0a.lifecycle = 'accepted'; },
  d => { d.slices.G0a.ci = { status: 'passed', sha: '0'.repeat(40) }; },
  d => { d.slices.G0a.independent_review = { status: 'passed', sha: '0'.repeat(40) }; },
  d => { d.slices.F1.lifecycle = 'implementing'; d.slices.G0a.lifecycle = 'implementing'; },
];
for (const mutate of mutations) {
  const changed = JSON.parse(JSON.stringify(ledger));
  mutate(changed);
  assert.throws(() => validate(changed));
}
console.log(`PASS: ${mutations.length} negative gate/status/dependency cases rejected`);
const requiredDocs = ['proposal.md', 'routes.md', 'ledger.yaml', 'email-retention.md', 'human-review.md', 'evidence/README.md'];
for (const relative of requiredDocs) assert.ok(fs.existsSync(path.join(root, relative)));
for (const role of ['orchestrator', 'worker', 'reviewer']) {
  const text = fs.readFileSync(path.join(repo, `.opencode/agents/migration-${role}.md`), 'utf8');
  const match = /^---\n([\s\S]*?)\n---\n([\s\S]+)$/.exec(text);
  assert.ok(match, `${role}: frontmatter`);
  const metadata = yaml.load(match[1]);
  assert.deepEqual(Object.keys(metadata).sort(), ['description', 'mode']);
  assert.equal(metadata.mode, role === 'orchestrator' ? 'all' : 'subagent');
  assert.ok(metadata.description.length > 20);
  for (const relative of requiredDocs) assert.ok(match[2].includes(relative), `${role}: missing pointer ${relative}`);
}
console.log('PASS: three agent frontmatters and all mandatory governance pointers');
console.log('LIMIT: static governance validation only; not OpenCode runtime discovery or application/CI parity');
