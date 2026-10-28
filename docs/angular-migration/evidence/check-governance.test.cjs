// Synthetic ledger states only. These fixtures never certify a real slice.
const assert = require('node:assert/strict');

module.exports = function runRegressionCases({validate, ledger, checkNames}) {
  const clone = value => JSON.parse(JSON.stringify(value));
  const evidence = 'evidence/README.md';
  const stages = ledger.lifecycle_values;
  const digest = id => String(Object.keys(ledger.slices).indexOf(id) + 1).padStart(40, '0');
  function fresh() {
    const data = clone(ledger);
    data.architecture_approval.baseline_verified = false;
    for (const [id, item] of Object.entries(data.slices)) {
      data.slices[id] = {...clone(data.defaults), title: item.title, acceptance: item.acceptance, depends_on: item.depends_on};
    }
    return data;
  }
  function advance(data, id, state) {
    const item = data.slices[id];
    item.lifecycle = state;
    item.execution_mode = 'implementation';
    item.worktree = `/tmp/opencode/governance-test/${id}-worker`;
    item.base_sha = 'b'.repeat(40);
    item.parent_sha = item.base_sha;
    const rank = stages.indexOf(state);
    if (rank >= stages.indexOf('candidate')) {
      item.candidate_sha = digest(id);
      for (const name of checkNames) item.checks[name] = {status: 'passed', evidence, sha: item.candidate_sha};
    }
    if (rank >= stages.indexOf('independently-reviewed')) {
      item.independent_review = {status: 'passed', reviewer: 'synthetic independent reviewer', sha: item.candidate_sha, worktree: `/tmp/opencode/governance-test/${id}-review`, evidence};
    }
    if (rank >= stages.indexOf('CI-passed')) {
      item.ci = {status: 'passed', sha: item.candidate_sha, run_url: 'https://example.invalid/synthetic-ci', required_gate_verified: true};
    }
    if (rank >= stages.indexOf('merged')) item.merge_sha = 'a' + item.candidate_sha.slice(1);
    if (state === 'human-review-pending' || state === 'accepted') {
      item.human_review = {status: state === 'accepted' ? 'accepted' : 'pending', owner: 'synthetic human', sha: item.merge_sha, evidence: state === 'accepted' ? evidence : null};
    }
    return item;
  }
  function foundation(data) {
    for (const id of ['G0a', 'G0b', 'G0']) advance(data, id, 'human-review-pending');
    data.architecture_approval.baseline_verified = true;
  }
  function stack(data, id, parentId, state = 'implementing') {
    const item = advance(data, id, state);
    item.parent_sha = data.slices[parentId].candidate_sha;
    item.provisional_stack = {
      authorized_by: 'synthetic orchestrator authorization', authorization_evidence: evidence,
      parents: [{id: parentId, candidate_sha: item.parent_sha, evidence, supplied_prerequisites: ['Synthetic prerequisite supplied by the pinned candidate.']}],
    };
    return item;
  }
  function acceptedPrerequisites(data) {
    for (const id of Object.keys(data.slices).filter(id => id !== 'C1')) advance(data, id, 'accepted');
    data.architecture_approval.baseline_verified = true;
  }
  const positives = [
    ['discovery allowed before G0a acceptance without implementation', d => { d.slices.G0b.execution_mode = 'discovery'; }],
    ['G0a implementation has no prerequisites', d => advance(d, 'G0a', 'implementing')],
    ['candidate may expose a failed check pending repair', d => { advance(d, 'G0a', 'candidate').checks.governance.status = 'failed'; }],
    ['independently reviewed with exact checked SHA', d => advance(d, 'G0a', 'independently-reviewed')],
    ['required CI-passed with independent review', d => advance(d, 'G0a', 'CI-passed')],
    ['merged before human assignment', d => advance(d, 'G0a', 'merged')],
    ['merged pending named human at merge SHA', d => advance(d, 'G0a', 'human-review-pending')],
    ['human rejection remains pending not accepted', d => { advance(d, 'G0a', 'human-review-pending').human_review.status = 'changes_requested'; }],
    ['accepted after full gates and human outcome', d => advance(d, 'G0a', 'accepted')],
    ['F1 ready after operational foundation while humans pending', d => { foundation(d); advance(d, 'F1', 'ready'); }],
    ['F1 implementation after operational foundation while humans pending', d => { foundation(d); advance(d, 'F1', 'implementing'); }],
    ['ordinary child need not wait for parent human acceptance', d => { foundation(d); advance(d, 'F1', 'human-review-pending'); advance(d, 'P1', 'ready'); }],
    ['G0b explicitly authorized on unaccepted G0a candidate', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a'); }],
    ['authorized feature stack pins unmerged parent after foundation', d => { foundation(d); advance(d, 'F1', 'candidate'); stack(d, 'P1', 'F1'); }],
    ['multi-parent stack pins every unmet dependency', d => {
      foundation(d); advance(d, 'F1', 'merged'); advance(d, 'P1', 'merged');
      advance(d, 'P2', 'candidate'); advance(d, 'P3', 'candidate');
      const item = stack(d, 'S1', 'P2');
      item.provisional_stack.parents.push({id: 'P3', candidate_sha: d.slices.P3.candidate_sha, evidence, supplied_prerequisites: ['Synthetic repository browsing prerequisite.']});
    }],
    ['stacked candidate may complete review and CI but not merge', d => { foundation(d); advance(d, 'F1', 'candidate'); stack(d, 'P1', 'F1', 'CI-passed'); }],
    ['reconciled child can merge with parent human pending', d => { foundation(d); advance(d, 'F1', 'human-review-pending'); advance(d, 'P1', 'merged'); }],
    ['C1 ready only after all dependency human acceptances', d => { acceptedPrerequisites(d); advance(d, 'C1', 'ready'); }],
    ['C1 accepted after all dependency and own human acceptances', d => { acceptedPrerequisites(d); advance(d, 'C1', 'accepted'); }],
    ['G0a runtime checks legitimately NA with governance passed', d => { const item = advance(d, 'G0a', 'merged'); item.checks.browser = {status: 'not_applicable', reason: 'Pure governance scope; no application changed.'}; }],
  ];
  for (const [name, arrange] of positives) {
    const data = fresh();
    arrange(data);
    assert.doesNotThrow(() => validate(data), name);
    console.log(`PASS positive: ${name}`);
  }
  const negatives = [
    ['approved dependency cannot be replaced', d => { d.slices.F1.depends_on = ['G0a']; }, /approved dependencies/],
    ['master target forbidden', d => { d.slices.G0a.pr_target = 'master'; }, /angular/],
    ['skipped is not a passing status', d => { d.slices.G0a.checks.governance.status = 'skipped'; }, /G0a: governance/],
    ['NA requires rationale', d => { d.slices.G0a.checks.browser.status = 'not_applicable'; }, /scope rationale/],
    ['missing required matrix entry rejected', d => { delete d.slices.G0a.checks.browser; }, /complete check matrix/],
    ['F1 implementing with G0 incomplete (review finding)', d => advance(d, 'F1', 'implementing'), /hard G0 foundation/],
    ['F1 ready with G0 incomplete', d => advance(d, 'F1', 'ready'), /hard G0 foundation/],
    ['baseline boolean cannot bypass incomplete G0b', d => { d.architecture_approval.baseline_verified = true; advance(d, 'F1', 'ready'); }, /baseline_verified/],
    ['G0a merged without review or CI (review finding)', d => { const item = advance(d, 'G0a', 'candidate'); item.lifecycle = 'merged'; item.merge_sha = 'a'.repeat(40); }, /independent review required/],
    ['merged with review but absent CI', d => { const item = advance(d, 'G0a', 'independently-reviewed'); item.lifecycle = 'merged'; item.merge_sha = 'a'.repeat(40); }, /required CI must pass/],
    ['merge identity required', d => { advance(d, 'G0a', 'merged').merge_sha = null; }, /merge identity required/],
    ['review must match candidate', d => { advance(d, 'G0a', 'merged').independent_review.sha = 'f'.repeat(40); }, /stale SHA/],
    ['CI must match candidate', d => { advance(d, 'G0a', 'merged').ci.sha = 'f'.repeat(40); }, /stale SHA/],
    ['required CI enforcement must be verified', d => { advance(d, 'G0a', 'merged').ci.required_gate_verified = false; }, /required CI gate enforcement/],
    ['truthy string cannot fake required CI enforcement', d => { advance(d, 'G0a', 'merged').ci.required_gate_verified = 'false'; }, /required CI gate enforcement/],
    ['failed required check prevents review/merge', d => { advance(d, 'G0a', 'merged').checks.governance.status = 'failed'; }, /incomplete required check/],
    ['blocked required check prevents review/merge', d => { advance(d, 'G0a', 'merged').checks.browser.status = 'blocked'; }, /incomplete required check/],
    ['not-run check prevents review/merge', d => { advance(d, 'G0a', 'merged').checks.browser.status = 'not_run'; }, /incomplete required check/],
    ['stale check cannot certify candidate', d => { advance(d, 'G0a', 'merged').checks.governance.sha = 'f'.repeat(40); }, /stale check/],
    ['passing check needs evidence', d => { advance(d, 'G0a', 'merged').checks.governance.evidence = null; }, /pass needs evidence/],
    ['G0b cannot waive operational browser baseline', d => { foundation(d); d.slices.G0b.checks.browser = {status: 'not_applicable', reason: 'Synthetic missing browser is not a waiver.'}; }, /incomplete required check/],
    ['review implementation checkout forbidden', d => { const item = advance(d, 'G0a', 'merged'); item.independent_review.worktree = item.worktree; }, /implementation worktree/],
    ['review main checkout forbidden', d => { advance(d, 'G0a', 'merged').independent_review.worktree = '/home/dr/projects/123view/'; }, /main checkout/],
    ['review worktree required', d => { advance(d, 'G0a', 'merged').independent_review.worktree = null; }, /isolated review worktree/],
    ['human acceptance cannot precede merge', d => { advance(d, 'G0a', 'candidate').human_review.status = 'accepted'; }, /human acceptance/],
    ['accepted needs accepted human result', d => { advance(d, 'G0a', 'accepted').human_review.status = 'pending'; }, /accepted/],
    ['accepted human SHA must match merge', d => { advance(d, 'G0a', 'accepted').human_review.sha = 'f'.repeat(40); }, /equal/],
    ['pending human owner required', d => { advance(d, 'G0a', 'human-review-pending').human_review.owner = null; }, /human owner/],
    ['C1 accepted with all dependencies unaccepted (review finding)', d => { for (const id of Object.keys(d.slices)) advance(d, id, id === 'C1' ? 'accepted' : 'human-review-pending'); d.architecture_approval.baseline_verified = true; }, /C1: all dependencies/],
    ['C1 ready cannot bypass one pending human', d => { acceptedPrerequisites(d); d.slices.R3.lifecycle = 'human-review-pending'; d.slices.R3.human_review.status = 'pending'; advance(d, 'C1', 'ready'); }, /C1: all dependencies/],
    ['ordinary readiness requires integrated dependency', d => { foundation(d); advance(d, 'F1', 'candidate'); advance(d, 'P1', 'ready'); }, /dependencies not integrated/],
    ['human rejected parent blocks dependent readiness', d => { foundation(d); advance(d, 'F1', 'human-review-pending').human_review.status = 'changes_requested'; advance(d, 'P1', 'ready'); }, /dependencies not integrated/],
    ['discovery cannot masquerade as implementation', d => { advance(d, 'G0b', 'implementing').execution_mode = 'discovery'; }, /discovery cannot advance/],
    ['provisional stack cannot bypass foundation', d => { advance(d, 'G0', 'candidate'); stack(d, 'F1', 'G0'); }, /G0: dependencies not integrated/],
    ['feature stacking cannot bypass absent G0 despite parent candidate', d => { advance(d, 'F1', 'candidate'); stack(d, 'P1', 'F1'); }, /hard G0 foundation/],
    ['stack authorization required', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a').provisional_stack.authorized_by = null; }, /stacking authorization/],
    ['stack authorization evidence required', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a').provisional_stack.authorization_evidence = null; }, /stacking authorization/],
    ['stale stack parent rejected', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a').provisional_stack.parents[0].candidate_sha = 'f'.repeat(40); }, /stale stack parent/],
    ['branch parent must pin declared candidate', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a').parent_sha = 'f'.repeat(40); }, /branch parent SHA/],
    ['stack must show supplied prerequisites', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a').provisional_stack.parents[0].supplied_prerequisites = []; }, /supplied prerequisites/],
    ['duplicate stack parents rejected', d => { advance(d, 'G0a', 'candidate'); const item = stack(d, 'G0b', 'G0a'); item.provisional_stack.parents.push(clone(item.provisional_stack.parents[0])); }, /every unmet dependency exactly once/],
    ['missing stack parent rejected', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a').provisional_stack.parents = []; }, /stack parent required/],
    ['incomplete parent checks cannot supply stack prerequisites', d => { advance(d, 'G0a', 'candidate').checks.governance.status = 'failed'; stack(d, 'G0b', 'G0a'); }, /incomplete required check/],
    ['stack cannot fake accepted parent', d => { advance(d, 'G0a', 'accepted'); stack(d, 'G0b', 'G0a'); }, /never fake accepted parents/],
    ['merged parent requires stack reconciliation', d => { advance(d, 'G0a', 'merged'); stack(d, 'G0b', 'G0a'); }, /never fake accepted parents/],
    ['provisional child cannot merge', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a', 'merged'); }, /reconcile stack before merge/],
    ['second implementation worker rejected', d => { advance(d, 'G0a', 'candidate'); stack(d, 'G0b', 'G0a'); advance(d, 'G0a', 'implementing'); }, /provisional parent must be an unmerged candidate|implementation/],
  ];
  // Two otherwise-ready feature workers isolate the concurrency failure itself.
  negatives.push(['two ready feature workers cannot implement concurrently', d => { foundation(d); advance(d, 'F1', 'merged'); advance(d, 'U1', 'implementing'); advance(d, 'U2', 'implementing'); }, /length <= 1/]);
  for (const [name, arrange, expectedError] of negatives) {
    const data = fresh();
    arrange(data);
    assert.throws(() => validate(data), expectedError, name);
    console.log(`PASS negative: ${name}`);
  }
  console.log(`PASS: ${positives.length} positive lifecycle scenarios; ${negatives.length} negative lifecycle/gate/status scenarios`);
};
