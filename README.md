# fronchfry.com

A simple PHP blog that renders pages and posts from Markdown files. No database, no framework, no build step.

## Requirements

- PHP 8.0+
- Apache with `mod_rewrite` enabled (or nginx with equivalent rewrite rules)

## Project Structure

```
fronchfry.com/
├── index.php          # Post listing (home page)
├── post.php           # Single post renderer
├── page.php           # Single page renderer
├── .htaccess          # Clean URL rewrite rules
├── includes/
│   ├── markdown.php   # Frontmatter parser + Markdown-to-HTML
│   ├── header.php     # Shared HTML header/nav
│   └── footer.php     # Shared HTML footer
├── posts/             # Blog post Markdown files
├── pages/             # Static page Markdown files
└── css/
    └── style.css
```

## Local Development

Using the PHP built-in server (no clean URLs — use query strings instead):

```bash
php -S localhost:8080
```

Then visit:
- `http://localhost:8080/` — post listing
- `http://localhost:8080/post.php?slug=hello-world` — single post
- `http://localhost:8080/page.php?slug=about` — single page

For clean URLs (`/posts/hello-world`), use Apache with `mod_rewrite` or nginx.

### Apache Virtual Host (example)

```apache
<VirtualHost *:80>
    ServerName fronchfry.test
    DocumentRoot /path/to/fronchfry.com
    <Directory /path/to/fronchfry.com>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### nginx (example)

```nginx
server {
    listen 80;
    server_name fronchfry.test;
    root /path/to/fronchfry.com;
    index index.php;

    location / {
        try_files $uri $uri/ @rewrites;
    }

    location @rewrites {
        rewrite ^/posts/([a-z0-9_-]+)/?$ /post.php?slug=$1 last;
        rewrite ^/pages/([a-z0-9_-]+)/?$ /page.php?slug=$1 last;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Writing Posts

1. Create a file in `/posts/` named with a URL-friendly slug, e.g. `my-new-post.md`
2. Add frontmatter at the top of the file:

```markdown
---
title: My New Post
date: 2026-04-18
description: A short summary shown on the post listing.
---

Your content here...
```

3. The post is immediately live at `/posts/my-new-post`

### Frontmatter Fields

| Field         | Required | Description                              |
|---------------|----------|------------------------------------------|
| `title`       | Yes      | Post title, shown in the heading and nav |
| `date`        | No       | Publication date (`YYYY-MM-DD`), used for sorting |
| `description` | No       | Short summary shown on the index listing |

## Writing Pages

Same as posts, but files go in `/pages/`:

1. Create `/pages/my-page.md` with frontmatter
2. Accessible at `/pages/my-page`

Pages are not listed on the index. Add links to them manually in `includes/header.php`.

## Supported Markdown

- Headings (`#` through `######`)
- **Bold**, *italic*, ***bold italic***
- `inline code` and fenced code blocks (with optional language)
- [Links](/) and ![images](/path/to/image.jpg)
- Unordered and ordered lists
- Blockquotes
- Horizontal rules (`---`)
- ~~Strikethrough~~
