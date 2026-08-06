# CropX WordPress Theme

A custom Gutenberg block theme for cropx.com. Each public-facing page section is a registered Gutenberg block that renders the same HTML as the static library at `/blocks/` in the project root.

## Status

**Phase 1: complete and verified end-to-end.** The theme installs, the build pipeline works, and the Hero block edits and renders correctly on both the editor and the front-end of a local WordPress 6.x install. Confirmed working on `cropx-2026-2` (Local by Flywheel, single-site).

What's in the theme right now:

- Theme scaffold (PHP templates, asset enqueueing, block registration)
- Build pipeline (`@wordpress/scripts` for JSX → JS compilation)
- One working block: **Hero** (`cropx/hero`)
- Design tokens (`styles/tokens.css`) loaded on both front-end AND inside the editor iframe
- Author font self-hosted via Fontshare's CDN (no local font files required)
- All image assets copied from the project root

The Hero is the proof-of-concept template — the remaining ~19 blocks follow exactly the same five-file pattern. See "Developing new blocks" below.

## Prerequisites

You need:

1. **Local WordPress dev environment** running on your machine. (You said you have one — Local by Flywheel, Studio, MAMP, Docker, etc. all work.)
2. **Node.js 18+** installed (`node --version` should print v18 or higher). Get it from [nodejs.org](https://nodejs.org/) if not.
3. The repo cloned locally (you already have it).

You do **not** need:
- ACF Pro (we're using core Gutenberg)
- Any other plugins for Phase 1

## One-time setup

From the project root:

```bash
cd wp-theme/cropx
npm install
```

This installs `@wordpress/scripts` and its dependencies. Takes ~1-2 minutes the first time. Creates a `node_modules/` folder (already gitignored).

## Building the blocks

```bash
npm run build
```

This compiles the JSX in `src/blocks/*/edit.js` into the `build/` folder. Run this once after `npm install` and again any time you (or I) change a block's source.

While developing, you can run `npm start` instead — it watches for file changes and rebuilds automatically.

## Installing the theme into WordPress

### Recommended — rsync from the project repo into the WP install

This is what we actually use during development. After `npm run build`, sync the theme folder over:

```bash
rsync -av --delete \
  --exclude='node_modules' --exclude='node_modules 2' \
  --exclude='.git' --exclude='.DS_Store' \
  ~/Documents/github/cropx-website-2026/wp-theme/cropx/ \
  "$HOME/Local Sites/cropx-2026-2/app/public/wp-content/themes/cropx/"
```

Why not symlink: Local by Flywheel runs the site inside a Docker container that doesn't reliably follow macOS symlinks pointing outside the project mount, so the theme would intermittently fail to load. Rsync is one extra command but it's deterministic.

Update the source path on the left and the destination path on the right (`cropx-2026-2`) for your machine. Add this command to a shell alias or VS Code task once you've got the paths right.

### Alternative — one-time copy (no ongoing dev)

If you just want to install the theme once and stop:

```bash
cp -R ~/Documents/github/cropx-website-2026/wp-theme/cropx "$HOME/Local Sites/cropx-2026-2/app/public/wp-content/themes/cropx"
```

You'll need to re-copy after every change. Use rsync above instead.

## Activating the theme

1. Open your local WordPress admin (`http://your-local-site/wp-admin/`)
2. Go to **Appearance → Themes**
3. Find "CropX" and click **Activate**

If you don't see the theme, double-check that the folder is named exactly `cropx` inside `wp-content/themes/`.

## Testing the Hero block

1. Create a new page: **Pages → Add New**
2. In the block inserter (the `+` button), search for "Hero" or look under the **CropX** category
3. Click "Hero" to insert it
4. Type into the eyebrow, heading, and subheading directly in the editor
5. Open the right sidebar and:
   - Pick a background image from the media library
   - Set the CTA button label and URL
   - Choose a segment accent color
6. Click **Publish** (or **Preview**) to see the rendered hero on the front-end

The front-end output should match the design of the static `blocks/hero.html` file.

## Developing new blocks

Every block is a folder under `src/blocks/<block-name>/` with five files:

| File | Purpose |
|---|---|
| `block.json` | Block metadata + attributes |
| `index.js` | `registerBlockType` call; imports `style.css` so webpack bundles it |
| `edit.js` | Editor UI (`RichText`, `InspectorControls`); imports `editor.css` |
| `render.php` | Front-end render template |
| `style.css` | Front-end + editor shared styles |

`src/blocks/hero/` is the reference implementation. Read all five files before porting a new block — the patterns matter.

### Workflow

1. Create the new block folder under `src/blocks/`.
2. Run `npm start` in another terminal to watch and rebuild on save.
3. After each rebuild, rsync to the WP install (see "Installing the theme" above).
4. Hard-refresh the editor (Cmd+Shift+R).
5. Test by inserting the block into a page.

### Critical gotchas — read before you start

Several non-obvious WordPress quirks were solved during the Hero port. **Skipping this list will cost you hours.** Full details and examples live in `/PROGRESS.md` (under "Phase 2 — WordPress block development gotchas") and `/CLAUDE.md` (under "Critical conventions and gotchas"). Greatest hits:

- Use plain string attributes — **never** `source: "html"` + `selector` on dynamic blocks.
- Enqueue shared CSS via `enqueue_block_assets` (not `enqueue_block_editor_assets`) so it reaches the editor iframe.
- Import CSS in JS source so webpack bundles it; reference the compiled output names (`style-index.css`, `index.css`) in `block.json`.
- `edit.js` must render the same DOM structure as `render.php`.
- Re-insert old block instances after `block.json` changes — stored attributes don't auto-migrate.
- Don't redeclare the Author font — it's loaded centrally; just use `var(--font-base)`.

### What's after Phase 2

Once all blocks are ported, **Phase 3** is pre-built page templates ("Segment Landing Page", "Product Page", etc.) that drop in pre-arranged sets of blocks. After that, switch back to multisite (deferred from earlier), staging review, and production launch.

## Folder structure reference

```
wp-theme/cropx/
├── style.css                  Theme metadata (required by WordPress)
├── functions.php              Bootstrap — loads inc/* files
├── header.php                 Front-end <head> + opening <body>
├── footer.php                 Front-end closing tags
├── index.php                  Default fallback template
├── page.php                   Static page template — renders the_content()
├── inc/
│   ├── theme-setup.php        Theme features + block category registration
│   ├── enqueue.php            Stylesheet enqueuing (front-end + editor)
│   └── blocks.php             Auto-registers blocks from build/blocks/
├── src/
│   └── blocks/
│       └── hero/              ← Phase 1 example block
│           ├── block.json     Block metadata (attributes, title, etc.)
│           ├── index.js       Source: registerBlockType call (compiled)
│           ├── edit.js        Source: editor UI component (compiled)
│           ├── render.php     Front-end render template
│           ├── style.css      Front-end + editor shared styles
│           └── editor.css     Editor-only style additions
├── build/                     Generated by `npm run build` — committed
│   └── blocks/                conditionally; see .gitignore comments
├── styles/
│   └── tokens.css             Design tokens (colors, type scale, etc.)
├── assets/                    Image / SVG assets, mirrored from project root
├── package.json               npm scripts + @wordpress/scripts dependency
└── .gitignore                 node_modules, build/, OS cruft
```

## Troubleshooting

**"npm install" fails with permission errors.** You probably need to run it as your normal user, not as root/sudo. If you accidentally ran with sudo, delete `node_modules/` and `package-lock.json`, then re-run.

**"Hero" doesn't appear in the block inserter.** Check that `npm run build` completed successfully (look for a `build/blocks/hero/index.js` file). Confirm you rsynced after building. Hard-refresh the editor (Cmd+Shift+R).

**`Updating failed. The response is not a valid JSON response` when saving a page.** WordPress's REST API isn't routing correctly. Go to Settings → Permalinks and pick "Post name" (or anything other than "Plain"). Save. The error goes away.

**The block typed content doesn't show on the front-end (defaults appear instead).** Almost always means an attribute in `block.json` has `source: "html"` + `selector`. Remove both — that combo silently drops user-typed content on dynamic blocks.

**The editor preview looks unstyled, even though the front-end renders correctly.** Global styles probably aren't reaching the editor iframe. Confirm `tokens.css` is enqueued via `enqueue_block_assets` (not `enqueue_block_editor_assets`). See `inc/enqueue.php`.

**Author font isn't rendering (text looks like Times or Helvetica).** Confirm `inc/enqueue.php` enqueues the Fontshare CSS on `enqueue_block_assets`. Hard-refresh the editor. Check DevTools Network tab for `api.fontshare.com` requests returning 200.

**The decorative pattern SVG on the Hero isn't visible.** It's intentionally subtle — `opacity(0.06)` from the original design. In DevTools, find `.hero-pattern` and temporarily bump opacity to verify it's there. If you want it more visible, change the value in `src/blocks/hero/style.css`.

**Background image won't save in the block.** Make sure the image is being picked from the WordPress media library (not pasted in via URL). The block stores both the WP attachment ID and its URL.

**Changes to `tokens/tokens.css` in the project root don't show up.** The theme has its own copy at `wp-theme/cropx/styles/tokens.css`. Copy the new version over: `cp tokens/tokens.css wp-theme/cropx/styles/tokens.css`, then rsync to the WP install.
