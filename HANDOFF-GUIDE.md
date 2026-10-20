# Handoff Guide — Start Here

This file is for whoever takes over design/development work on the CropX website after Lauren. It's written for someone who may have little or no coding/design background — the goal is to get you oriented in ten minutes, not to teach you WordPress development.

If you only read one section, read **"Your first session"** near the bottom.

## What the other files in this repo are for

| File | Audience | Purpose |
|---|---|---|
| `HANDOFF-GUIDE.md` (this file) | You, a human | Orientation and how to work with Claude on this project |
| `CLAUDE.md` | Claude (an AI) | The technical rulebook — conventions, gotchas, commands. Claude reads this automatically at the start of a session in this repo. |
| `PROGRESS.md` | Claude (an AI), and you if curious | A running diary of literally everything that's been built or fixed, in chronological order — the single richest source of "why is it built this way" and "has this broken before." |

You don't need to read CLAUDE.md or PROGRESS.md yourself unless you're curious — Claude reads them for you. But if something ever feels confusing or you want the full story behind a decision, PROGRESS.md is where it lives.

## What this project actually is, in one paragraph

The CropX website is a fully custom WordPress theme — every section of every page (hero banners, testimonial carousels, pricing tables, the nav bar) is a hand-built component called a "Gutenberg block," written specifically for this site rather than bought off the shelf. This gives total design control but means any visual change involves real code, a compile ("build") step, and a careful deploy process — not just clicking around in a page builder. Claude does the actual code-writing; your job is to describe what you want and follow the safety steps below.

## The skills that came with this handoff

Five Claude "skills" should have come with this repo (as `.skill` files) — install all five in whatever Claude product you're using:

- **cropx-wordpress-editing** — the core workflow, deploy process, and every hard-won lesson from this project's history.
- **cropx-design-standards** — the full visual design system reference (colors, type, spacing, components).
- **cropx-brand-guidelines-2026** — the official brand palette and typography.
- **cropx-image-optimizer** — the exact image resize/compress pipeline this project uses.
- **case-study-final-stylistic-editing-pass** — house style for editing customer case studies.

A companion Word document ("CropX Handoff Playbook") walks through all of this in more narrative detail, including a glossary — read that first if this file feels too terse.

## Your first session

When you start working with Claude on this project for the first time:

1. Make sure Claude has access to this repo/folder (however your Claude product connects to files).
2. Ask Claude to read `CLAUDE.md` and `PROGRESS.md` before doing anything else. It should also read the tail end of `PROGRESS.md` first since that's the most recent state.
3. Install the five skills above if you haven't already.
4. Describe your first request in plain language. Don't worry about using the right technical terms — describe the outcome you want, attach a screenshot if you have a reference, and let Claude ask clarifying questions.

## The one thing to never skip

**Every deploy to the live/staging server should go through small, targeted zip files uploaded via WP File Manager in wp-admin — never one giant zip of the whole theme.** This project hit a real incident where a full-theme re-upload left the server in a silently broken, half-updated state. Small patch zips, always paired with a "revert" zip, is the pattern that's worked reliably. Claude knows this from the `cropx-wordpress-editing` skill, but it's worth you knowing too, so you can push back if a session ever suggests otherwise.

## Questions this guide doesn't answer

This guide (and the skills) cover design, content, and front-end development work. They don't cover server administration, domain/DNS, hosting billing, or security — if you hit one of those, that's a sign to loop in whoever handles IT/hosting for CropX rather than trying to solve it through Claude alone.

Good luck — you've got a lot of careful, documented work to build on.
