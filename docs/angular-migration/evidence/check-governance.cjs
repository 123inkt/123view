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
const checkNames = ['governance', 'npm_ci', 'production_build', 'frontend_lint', 'angular_components', 'backend_unit', 'backend_functional', 'backend_integration', 'php_static', 'twig_container_email', 'browser'];
const humanStates = ['unassigned', 'pending', 'changes_requested', 'accepted'];
const rank = item => lifecycle.indexOf(item.lifecycle);
const integrated = item => rank(item) >= lifecycle.indexOf('merged');
const usableDependency = item => integrated(item) && item.human_review.status !== 'changes_requested' && item.blockers.length === 0;
const nonempty = value => typeof value === 'string' && value.trim().length > 0;

function completeChecks(id, item) {
  for (const name of checkNames) {
    const check = item.checks[name];
    // G0b certifies the operational baseline: none of its matrix may be waived.
    const applicable = id === 'G0b' || name === 'governance';
    assert.ok(check.status === 'passed' || (!applicable && check.status === 'not_applicable'), `${id}: incomplete required check ${name}`);
    if (check.status === 'passed') {
      assert.ok(sha(item.candidate_sha), `${id}: missing candidate SHA`);
      assert.equal(check.sha, item.candidate_sha, `${id}: stale check ${name}`);
    }
  }
}

function validate(data) {
  assert.equal(data.schema_version, 2);
  assert.equal(data.target_branch, 'angular');
  assert.equal(data.max_active_implementation_workers, 1);
  assert.deepEqual(data.check_status_values, checkStates);
  assert.deepEqual(data.lifecycle_values, lifecycle);
  assert.deepEqual(data.human_status_values, humanStates);
  assert.deepEqual(Object.keys(data.slices).sort(), Object.keys(expected).sort());
  for (const [id, item] of Object.entries(data.slices)) {
    assert.deepEqual(item.depends_on, expected[id], `${id}: approved dependencies`);
    assert.equal(item.pr_target, 'angular');
    assert.ok(lifecycle.includes(item.lifecycle));
    assert.ok(['implementation', 'discovery'].includes(item.execution_mode), `${id}: execution mode`);
    if (item.execution_mode === 'discovery') {
      assert.equal(item.lifecycle, 'proposed', `${id}: discovery cannot advance implementation`);
      assert.equal(item.candidate_sha, null, `${id}: discovery cannot create a candidate`);
    }
    assert.ok(Array.isArray(item.acceptance) && item.acceptance.length > 0);
    assert.ok(item.acceptance.every(x => typeof x === 'string' && x.length > 20));
    for (const field of Object.keys(data.defaults)) assert.ok(field in item, `${id}: ${field}`);
    for (const field of ['base_sha', 'parent_sha', 'candidate_sha', 'merge_sha']) {
      assert.ok(item[field] === null || sha(item[field]), `${id}: ${field}`);
    }
    assert.deepEqual(Object.keys(item.checks).sort(), [...checkNames].sort(), `${id}: complete check matrix required`);
    assert.ok(Array.isArray(item.blockers));
    for (const [name, check] of Object.entries(item.checks)) {
      assert.ok(checkStates.includes(check.status), `${id}: ${name}`);
      if (check.status === 'passed') assert.ok(nonempty(check.evidence), `${id}: pass needs evidence`);
      if (check.status === 'not_applicable') assert.ok(nonempty(check.reason), `${id}: scope rationale required`);
    }
    for (const record of [item.ci, item.independent_review]) {
      assert.ok(checkStates.includes(record.status));
      if (record.status === 'passed') {
        assert.ok(sha(item.candidate_sha));
        assert.equal(record.sha, item.candidate_sha, `${id}: stale SHA`);
      }
    }
    if (item.ci.status === 'passed') {
      assert.ok(nonempty(item.ci.run_url), `${id}: CI run URL required`);
      assert.equal(item.ci.required_gate_verified, true, `${id}: required CI gate enforcement must be verified`);
      completeChecks(id, item);
    }
    if (item.independent_review.status === 'passed') {
      assert.ok(nonempty(item.independent_review.reviewer) && nonempty(item.independent_review.evidence));
      assert.ok(path.isAbsolute(item.independent_review.worktree || ''), `${id}: isolated review worktree required`);
      assert.notEqual(path.resolve(item.independent_review.worktree), path.resolve(item.worktree || '.'), `${id}: review cannot use implementation worktree`);
      assert.notEqual(path.resolve(item.independent_review.worktree), '/home/dr/projects/123view', `${id}: review cannot use main checkout`);
      completeChecks(id, item);
    }
    assert.ok(data.human_status_values.includes(item.human_review.status));
    if (rank(item) >= lifecycle.indexOf('candidate')) assert.ok(sha(item.candidate_sha), `${id}: candidate identity required`);
    if (rank(item) >= lifecycle.indexOf('independently-reviewed')) {
      assert.equal(item.independent_review.status, 'passed', `${id}: independent review required`);
      completeChecks(id, item);
    }
    if (rank(item) >= lifecycle.indexOf('CI-passed')) assert.equal(item.ci.status, 'passed', `${id}: required CI must pass`);
    if (integrated(item)) {
      assert.ok(sha(item.merge_sha), `${id}: merge identity required`);
      assert.equal(item.provisional_stack, null, `${id}: reconcile stack before merge`);
    } else {
      assert.equal(item.merge_sha, null, `${id}: unmerged state with merge identity`);
    }
    if (item.human_review.status === 'accepted') assert.equal(item.lifecycle, 'accepted', `${id}: human acceptance requires accepted lifecycle`);
    if (item.human_review.status === 'changes_requested') assert.equal(item.lifecycle, 'human-review-pending', `${id}: human rejection must remain pending`);
    if (item.lifecycle === 'human-review-pending') {
      assert.ok(['pending', 'changes_requested'].includes(item.human_review.status), `${id}: human review pending decision required`);
      assert.ok(nonempty(item.human_review.owner), `${id}: human owner required`);
      assert.equal(item.human_review.sha, item.merge_sha, `${id}: human runtime must match merge`);
    }
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
  // Foundation is a hard gate, not a stackable feature prerequisite. Human review
  // can still be pending, but operational checks, review, CI and merge must exist.
  const baselineReady = ['G0a', 'G0b', 'G0'].every(id => usableDependency(data.slices[id]));
  if (data.architecture_approval.baseline_verified) {
    assert.ok(['G0a', 'G0b'].every(id => usableDependency(data.slices[id])), 'baseline_verified requires integrated G0a and G0b');
  }
  for (const [id, item] of Object.entries(data.slices)) {
    if (item.lifecycle === 'proposed') {
      assert.equal(item.provisional_stack, null, `${id}: proposed discovery is not authorized implementation`);
      continue;
    }
    assert.equal(data.architecture_approval.status, 'approved', `${id}: architecture approval required`);
    if (!['G0', 'G0a', 'G0b'].includes(id)) {
      assert.ok(data.architecture_approval.baseline_verified && baselineReady, `${id}: hard G0 foundation gate`);
    }
    const unmet = item.depends_on.filter(dep => !usableDependency(data.slices[dep]));
    if (id === 'C1') {
      assert.ok(item.depends_on.every(dep => data.slices[dep].lifecycle === 'accepted' && data.slices[dep].human_review.status === 'accepted'), 'C1: all dependencies and human outcomes must be accepted');
    }
    const stack = item.provisional_stack;
    if (stack === null) {
      assert.deepEqual(unmet, [], `${id}: dependencies not integrated`);
      continue;
    }
    assert.ok(!['G0', 'C1'].includes(id) && !integrated(item), `${id}: stacking cannot bypass aggregate/cleanup/merge gates`);
    assert.ok(nonempty(stack.authorized_by) && nonempty(stack.authorization_evidence), `${id}: explicit stacking authorization required`);
    assert.ok(Array.isArray(stack.parents) && stack.parents.length > 0, `${id}: stack parent required`);
    assert.deepEqual(stack.parents.map(parent => parent.id).sort(), [...unmet].sort(), `${id}: pin every unmet dependency exactly once, never fake accepted parents`);
    for (const parent of stack.parents) {
      const dependency = data.slices[parent.id];
      assert.ok(rank(dependency) >= lifecycle.indexOf('candidate') && !integrated(dependency), `${id}: provisional parent must be an unmerged candidate`);
      assert.ok(sha(parent.candidate_sha) && parent.candidate_sha === dependency.candidate_sha, `${id}: stale stack parent SHA`);
      assert.ok(nonempty(parent.evidence) && Array.isArray(parent.supplied_prerequisites) && parent.supplied_prerequisites.length > 0 && parent.supplied_prerequisites.every(nonempty), `${id}: document supplied prerequisites`);
      completeChecks(parent.id, dependency);
    }
    assert.ok(stack.parents.some(parent => parent.candidate_sha === item.parent_sha), `${id}: branch parent SHA must pin a declared parent`);
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
require('./check-governance.test.cjs')({validate, ledger, checkNames});
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
  if (role !== 'worker') {
    assert.match(match[2], /separate isolated review worktree/i, `${role}: explicit independent review isolation`);
    assert.match(match[2], /candidate-only/, `${role}: candidate runtime ownership`);
    assert.match(match[2], /instruction-only|instruction-level|instructions, not tool-enforced/, `${role}: permission limitation`);
  }
}
console.log('PASS: three agent frontmatters and all mandatory governance pointers');
console.log('LIMIT: static governance validation only; not OpenCode runtime discovery or application/CI parity');
