# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

Pure PHP 8.0+, no framework, no database, no build step. Content is file-based Markdown with YAML frontmatter.

## Development

```bash
# Local server (no clean URLs — access via query params)
php -S localhost:8080

# Access posts:  http://localhost:8080/post.php?slug=<slug>
# Access pages:  http://localhost:8080/page.php?slug=<slug>
# Home:          http://localhost:8080/
```

Clean URLs (`/posts/<slug>`, `/pages/<slug>`) require Apache with `mod_rewrite` or equivalent nginx rewrites (see README).

## Architecture

**Routing** via `.htaccess` rewrites:
- `/` → `index.php`
- `/posts/<slug>` → `post.php?slug=<slug>`
- `/pages/<slug>` → `page.php?slug=<slug>`

**Content pipeline**: `includes/markdown.php` provides all data access — `parse_frontmatter()`, `markdown_to_html()`, `load_post()`, `load_page()`, `get_all_posts()`. The markdown parser is a custom regex implementation (no third-party library).

**Templates**: `includes/header.php` and `includes/footer.php` wrap every page. Navigation links in `header.php` are manually maintained — pages are not auto-listed.

**Content files**:
- `posts/*.md` — blog posts, auto-sorted newest-first by `date` frontmatter field (YYYY-MM-DD)
- `pages/*.md` — static pages, not indexed, linked manually in nav

**Deployment**: `deploy.php` handles GitHub webhooks — validates HMAC-SHA256 signature against `.deploy-secret`, then runs `git pull` on push to main/master.

## Authoring Content

Every `.md` file must begin with a `---` delimited frontmatter block:

```yaml
---
title: Post Title
date: 2024-01-15
description: Optional description
---
```

Post filenames become the URL slug (e.g., `posts/my-new-post.md` → `/posts/my-new-post`). Slugs are sanitized to `[a-z0-9_-]`.
