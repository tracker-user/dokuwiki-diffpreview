# DiffPreview plugin for DokuWiki — local fork

Local fork of the [DiffPreview plugin](https://www.dokuwiki.org/plugin:diffpreview) (tqdv/dokuwiki-diffpreview), tracking upstream `1.3.1` (2023-06-18). Adds a **"Changes"** button to the page editor that shows a side-by-side diff of your unsaved edits against the saved page.

This fork fixes one real bug, removes dead code, and pins the version.

## Why a fork

Upstream is effectively dormant — the last release is 1.3.1 (2023-06-18) and the GitHub repository has no open issues or pull requests. Its dokuwiki.org compatibility list stops at **Jack Jackrum**, two releases before Librarian, so it was never verified against the current DokuWiki. The plugin is small and basically sound, but it carries one genuine bug that surfaces as PHP 8 warnings, so rather than depend on an unmaintained upstream this is a local fork with the fix applied and the version pinned.

## What changed in the local fork

### Bug fix: `$INFO` undefined in `helper/changes.php`

`tplContent()` rendered the diff like this:

```php
public function tplContent() {
    global $PRE, $TEXT, $SUF;        // <- $INFO is NOT declared global
    ...
    ((new dokuwiki\Ui\PageDiff($INFO['id']))
        ->compareWith(con($PRE,$TEXT,$SUF))
    ...
```

`$INFO` was never declared `global` in this method, so it was an **undefined local variable**. On PHP 8 that produces two warnings on *every* preview — `Undefined variable $INFO` and `Trying to access array offset on value of type null` — and `$INFO['id']` evaluates to `null`.

The bug was *functionally* masked: `dokuwiki\Ui\PageDiff::__construct($id = null)` does its own `global $INFO; if (!isset($id)) $id = $INFO['id'];`, so passing `null` made PageDiff recover with the correct id anyway. But the warnings are real, and on a wiki with `display_errors` enabled they corrupt the rendered page.

The fix drops the broken explicit argument entirely and calls `new dokuwiki\Ui\PageDiff()` with no argument — PageDiff then resolves the current page id itself, correctly and warning-free. This is both the smallest and the most correct fix: the diff preview is always for the page being edited, which is exactly what PageDiff's default resolves to.

### Dead-code removal in `helper/changes.php`

Upstream's helper class also carried a constructor and four methods — `minimumPermission()`, `checkPreconditions()`, `preProcess()`, `getActionName()` — plus a `$preview` property. These were scaffolding for an unrealised "what if DokuWiki had an `Action\Changes` class" design (the class docblock even said so). **Nothing ever called them** — `action.php` only ever invokes `tplContent()`. Worse, the constructor instantiated a `dokuwiki\Action\Preview` object on every single preview, purely to back those unused methods.

All of it was removed. The helper is now a lean class with the single method that is actually used. This also eliminates a pointless object instantiation on every preview and a stale "this class is currently unused" comment that 1.3.1 had already made untrue.

### Light modernization of `action.php`

`action.php` was already in good shape (proper visibility, clean structure, deliberate multi-release branching). Two small touch-ups only:

- `array()` → `[]` short syntax (two spots: the edit-button attributes and the legacy `$draft` array).
- Dropped an unused `$INFO` from a `global` declaration in `_action_act_preprocess()` (the method only uses `$ACT`).

The multi-release detection logic (`is_a()`, `class_exists()`, `function_exists()` branches that let one codebase serve Hogfather through Librarian) was deliberately left untouched — it is the plugin's whole compatibility strategy.

### Update suppression

`plugin.info.txt` `date` set to `2077-06-18` (original day/month, year bumped to 2077). The Extension Manager's `isUpdateAvailable()` compares the installed date against upstream's as a string, so an Update is never offered — clicking it would otherwise overwrite this fork with the unmodified upstream. Matches the convention used by the other forks in this collection.

## What did NOT change

- The "Changes" button, its behaviour, and the JavaScript (`script.js`) — unchanged.
- The multi-release compatibility branching in `action.php`.
- The 6 language files, `CHANGELOG.md`.
- The plugin's public surface — only `tplContent()` was ever public-and-used, and it still is.

## Verified against

DokuWiki `2025-05-14b "Librarian"` — confirmed that the classes and methods the plugin relies on still exist with the expected visibility (`dokuwiki\Action\Edit::checkPreconditions()`/`preProcess()` are still `public`; `dokuwiki\Ui\PageDiff`, `dokuwiki\Ui\Editor`, `con()` all present). PHP lint clean under PHP 8.3; the "Changes" preview renders without the previously-emitted `$INFO` warnings.

## Install

Drop the folder into `lib/plugins/diffpreview/`, or use Admin → Extension Manager → Manual Install to upload the zip.

## License

GPL-2.0-or-later, matching the original plugin.
