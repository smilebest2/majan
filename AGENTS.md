# Codex instructions for the three-player mahjong app

## Canonical repository

- This directory is the existing canonical working copy. Reuse it for every future change.
- Do not create a new clone, worktree, repository, or generated workspace for this app unless the user explicitly asks.
- Remote: `origin` -> `https://github.com/smilebest2/majan.git`
- Branch used for the personal published app: `master`
- Public URL: `https://smilebest2.github.io/majan/`
- GitHub Pages publishes the static site from `docs/`.

## Before and after changes

- Start with `git status --short --branch`; preserve unrelated or uncommitted user changes.
- Keep changes scoped to the requested feature or fix.
- Run both regression tests:
  - `node tests/sanma-scoring.cjs`
  - `node tests/sanma-engine-smoke.cjs`
- For interaction changes, also test the site in a phone landscape viewport (approximately 844 x 390) and verify an actual tile tap.
- When `docs/game.js` changes, update the cache-busting version on its script URL in `docs/index.html` so GitHub Pages clients receive the new code immediately.
- When publication is requested, commit intentionally to `master`, push `origin master`, wait for the Pages build, and verify the public URL.

## Game-specific notes

- Tile images are in `public/img/hai/`; the user owns these images.
- The playable static implementation is under `docs/`, not the legacy Laravel routes.
- `docs/scoring.js` contains scoring and dora calculation. The center of the table displays dora indicators; the actual dora is the following tile.
- Avoid changing sanma rules silently. State assumptions for rules such as open tanyao, north extraction, kan, riichi, honba, and match continuation.

## Git environment

- The repository may trigger Git's ownership safety check in sandboxed sessions. The canonical path should be registered once in the user's global `safe.directory` setting; do not work around it by cloning again.
- GitHub CLI authentication for account `smilebest2` is the preferred credential source. Do not place tokens in files, commits, command output, or repository configuration.
