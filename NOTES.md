# TECHi WordPress Data Model Notes

## Articles

TECHi's editorial articles map naturally to WordPress's native `post` content model:

- `wp_posts` stores the article title, content, status, date, post type and author ID.
- `wp_users` provides the author identity.
- `post_author` links an article to its author.
- Categories and tags are native WordPress taxonomies.
- Featured images are represented through WordPress media and the `_thumbnail_id` relationship.
- Additional editorial attributes can use post meta where required.

## Authors

Named authors can be represented by WordPress users. Profile information can be extended through user meta or an author profile layer. An author's story count does not need to be stored as a manually maintained number; it can be derived from published posts assigned to that author.

## Review content

A review is kept as its own `techi_review` post type because editorial review data has a distinct structured model: score, verdict, Pros, Cons, price and review date. The review still benefits from WordPress's native title, content, author, revisions, taxonomy, permissions and REST infrastructure.

The `techi_review_category` taxonomy is hierarchical, matching the way WordPress already models category trees.

## What WordPress already gives us

Core already provides:

- Posts and custom post types
- Users and authors
- Hierarchical taxonomies
- Post meta
- Revisions
- Block editor
- REST API infrastructure
- Querying and pagination
- Capabilities and role checks
- Nonces
- Media and featured images
- Permalinks and archives

The plugin therefore adds only the structured review-specific layer instead of rebuilding editorial primitives.
