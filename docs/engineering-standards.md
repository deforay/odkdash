# Engineering Standards

The bar this codebase is held to, and the review that enforces it.

## 1. Adversarial review

Run before pushing, not after somebody asks:

```bash
bin/dev/review                  # the working branch against master
bin/dev/review <commit-sha>     # one commit
bin/dev/review --uncommitted    # the working tree, before committing
bin/dev/review --verify <...>   # add a second pass that tries to refute the first's findings
bin/dev/review --pending        # list commits not yet recorded as reviewed
bin/dev/review --since-last     # review everything since the last recorded commit
bin/dev/review --mark [sha]     # record a commit (default HEAD) as reviewed
```

The last three keep a ledger of reviewed commits in
`$(git rev-parse --git-common-dir)/reviewed-commits.txt`. The ledger is per clone and never
committed. Set `REVIEW_LEDGER` to keep it elsewhere.

`--verify` exists because one pass that both finds and reports has nothing pushing back on
it, and that is where the false positives are. The second pass is given the findings and
one job — break them — and reports only what survives, with the file and line that settles
each. It doubles the cost of a run, so it is opt-in; `REVIEW_VERIFY=1` in `.env` makes it
the default.

The reviewing CLI is named by `$REVIEW_AGENT`, read from the shell environment, falling
back to the untracked `.env`. It is deliberately not written down here: the tool can be
swapped without editing anything, and no vendor name enters the repository. `REVIEW_AGENT`
is a command line rather than a bare binary, so a reviewer that wants a subcommand and one
that wants a flag are both a setting; set `REVIEW_AGENT_STDIN=1` for a reviewer that reads
its prompt from standard input. See `.env.example`.

`REVIEW_AGENT_FALLBACK` names a second reviewer for when the first is out of usage. The
script switches only when the first exits non-zero with a message matching
`REVIEW_LIMIT_PATTERN` (default `usage limit|rate limit|quota`), so a finding that mentions
a limit never triggers it, and the fallback then handles the verify pass too. With a
fallback configured, a reviewer that exits cleanly without printing anything counts as not
having reviewed, and a run that neither reviewer finishes records nothing in the ledger.

The review is a local step, not a CI job — the reviewing CLI is authenticated on your
machine.

**The review brief** — single source of truth, extracted verbatim by the script, so it cannot drift:
> "You are reviewing a change to odkdash, a PHP dashboard that pulls SPI-RT audit submissions (v3 and v6 forms) from ODK Central, lets reviewers approve and correct them, and reports on them by country, province, district and facility. Each deployment is a separate instance with its own database, upgraded in place by bin/upgrade.sh. Do not summarize the code. Find: (1) any read of audit or submission data that skips the user's location mapping (`applyV6LocationScope`, `userMappingType`, `userMappedIds`), and any route or action reachable without an entry in the role allow-list (`Model/Acl.php`, checked in `Module::onBootstrap`); (2) SQL built by concatenating request or submission data instead of binding it; (3) any ODK submission value reaching an HTML sink — a view, a grid cell, a PDF built with writeHTML — without `CommonService::escapeHtml`, or escaped with the wrong helper for its context (HTML body, attribute, JS string); (4) anything that can silently lose or duplicate audit data — a Central sync that is not safe to re-run, an update that blanks a column it did not mean to touch, a delete of audits or facility links the user did not ask for, a duplicate check that drops a legitimate submission; (5) a record that cannot be resolved (an audit with no facility, a facility with no location) hidden from the list or guessed at, rather than shown and findable with a filter; (6) schema changes made anywhere but `migrations/`, migrations that are not re-runnable on both fresh and upgraded installs, and a new migration without the matching `version` bump in composer.json; (7) a defect fixed in the v3 code while the v6 code carries the same copy (or the reverse); (8) a password, token or other secret placed on a command line, echoed, logged, or written to a file that outlives the run; (9) strings shown in the web UI that bypass the translator (console output is English and not translated). Carry the evidence: every claim about code names the file and line and quotes the line it rests on. If you cannot find the line that proves a claim, report the finding as UNVERIFIED and say what you looked for -- that is a third outcome alongside a finding and a clear, and it is always preferable to asserting on thin evidence. When a hand-maintained list is missing an entry, trace the whole path and report every entry it is missing, not the first. If the commit message or a comment already addresses a concern, say why that reasoning is insufficient rather than restating the concern. Report a defect outside the diff separately, not ranked among the diff's findings. Rank findings by consequence, not by feel: P1 is an instance losing audit data, leaking another country's data, or being unable to upgrade; everything else starts at P2. If you find nothing in a category, say 'clear' -- don't pad, and never reach for a finding to avoid an empty category."

**Where the second opinion matters most:** location scoping and the allow-list, the Central
sync commands, approval and correction of audits, every migration, and `bin/upgrade.sh`.
Routine CRUD does not need double review — don't ritualize it into overhead.

## 2. What counts as a finding

A defect with a failure scenario: concrete inputs or state, and the wrong output or lost data
that results. "This could be cleaner" is not a finding. A trade-off already recorded in the
docs is a rebuttal, not a fix.

Address or explicitly rebut every finding before merging. A rebuttal is a sentence saying why
the code is right, not silence.

## 3. Standing invariants

These are the rules the brief is derived from, so a change can be checked against them
without running a review.

- **Location scope.** A user mapped to countries, provinces or districts sees only audits
  inside that mapping. A query that skips it is a data leak, not a style issue.
- **Every route is on the allow-list.** `Module::onBootstrap` refuses any controller and
  action the role has no entry for, so a new action needs its resource and privilege rows.
- **Submission data is escaped where it is printed.** ODK values are whatever a field
  worker typed. They pass through `CommonService::escapeHtml` before reaching a view, a
  grid cell or a PDF; cells the code builds itself are assembled from escaped parts.
- **Schema changes live in `migrations/`.** Migrations replay on fresh installs too, so they
  must be re-runnable and must not assume an upgraded database. A new migration means a
  `version` bump in `composer.json`. See `migrations/README.md`.
- **Unresolvable records are surfaced, not hidden.** An audit that cannot be tied to a
  facility still appears, flagged, with a filter to find it, and counts in the total.
- **v3 and v6 are siblings.** A defect found in one form's code is usually present in the
  other. Fix both.
- **Secrets stay off the command line.** A password typed at a prompt is read without echo;
  one handed to MySQL goes through an option file that is removed after use.

## 4. Before you push

- `composer qc` clean: composer.json valid, every PHP file and shell script lints, no security advisories.
- `php bin/migrate --dry-run` clean when a migration changed.
- `bin/dev/review` run, and every finding addressed or rebutted.
- Shell scripts under `bin/` run, not just read. `bash -n` proves syntax and nothing else.
