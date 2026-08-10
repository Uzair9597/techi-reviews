# TECHi Review Desk

A self-contained WordPress plugin for structured technology product reviews.

## Requirements

- WordPress 6.x
- PHP 8.x
- No paid plugins or external services required

## Installation

1. Copy the `techi-reviews` folder into `wp-content/plugins/`.
2. Activate **TECHi Review Desk** from Plugins.
3. Open **Reviews** in the WordPress admin.
4. Add review categories and create a review.
5. For demo data, open **Tools → TECHi Reviews** and seed 25 synthetic reviews.
6. Public REST endpoint: `/wp-json/techi/v1/reviews`

## Implemented

- `techi_review` public custom post type with archive, block editor and REST support.
- Hierarchical `techi_review_category` taxonomy.
- Custom review editor UI without ACF or another field framework.
- Product name, score, verdict, repeatable pros/cons, PKR price and review date.
- Sanitization and meta authorization callbacks.
- Nonce and capability checks on review writes and demo tools.
- Score card prepended only to the main content of single review pages.
- Schema.org Review JSON-LD.
- Paginated public REST endpoint with category, min score, search, ordering and bounded per-page arguments.
- Tools screen for seeding 25 synthetic reviews and safely deleting only synthetic records.
- Sortable Score column and admin category filter.
- Vanilla JavaScript repeatable fields.

## Not implemented

- Transient caching and cache invalidation.
- `[techi_review_grid]` shortcode.

These were intentionally left for the end of the timebox so the core requirements could be completed and tested first.

## Key decisions

### Native WordPress APIs

The plugin uses `register_post_type`, `register_taxonomy`, post meta, meta boxes, `WP_Query`, `register_rest_route`, nonces and capability checks. This keeps the plugin self-contained and avoids a dependency on a field framework.

### Repeatable Pros and Cons

Pros and cons are stored as sanitized arrays in post meta. This keeps the data simple, preserves order, and makes it easy to render or return through the REST API.

### Synthetic data marker

Seeded records receive `_techi_synthetic = 1`. The delete action only removes records carrying this marker, so it cannot accidentally remove real reviews.

### Hook choice

The score card uses `the_content` with `is_singular('techi_review')`, `in_the_loop()` and `is_main_query()` checks. This confines the card to the primary single-review content rather than changing excerpts, feeds or unrelated post types.

## Security notes

- Admin review saves require a nonce and `edit_post` capability.
- Review meta has explicit authorization callbacks.
- REST arguments have sanitization and validation callbacks.
- REST `per_page` is bounded to 50.
- Frontend values are escaped before output.
- JSON-LD is generated using `wp_json_encode()`.
- Demo deletion is nonce and capability protected and only targets marked synthetic posts.

## Testing checklist

- Activate on a stock WordPress 6.x install with a default theme.
- Create a review and verify all fields survive save/reload.
- Add/remove multiple Pros and Cons.
- Try scores below 0, above 10 and more than one decimal place.
- Confirm the verdict is limited to 140 characters.
- Open a single review and verify the score card appears.
- Open a normal post and verify the card does not appear.
- Check the REST endpoint with pagination and filters.
- Try `per_page=51` and confirm validation rejects it.
- Seed 25 records and delete them from Tools.
- Verify only synthetic records are deleted.
- Check the page source for valid Review JSON-LD.
- Enable `WP_DEBUG` and confirm there are no plugin notices/warnings during the above tests.

## Versions tested

- WordPress: 6.4.2
- PHP: 8.x
- Theme: default WordPress theme

The plugin was tested locally on WordPress 6.4.2, matching the assessment's requested WordPress 6.x baseline. It uses standard WordPress APIs and does not depend on a paid plugin or external service.

## Time spent

Record the actual focused development/testing time before submission.

## AI disclosure

AI assistance was used during development for planning, API/reference checks, code review and implementation assistance. The submitted code was locally reviewed, tested and explained by the author before submission.

## Improvements with more time

- Add transient caching with invalidation on review save/delete.
- Add the review grid shortcode with progressive enhancement.
- Add automated PHPUnit/WordPress integration tests.
- Add richer admin validation messages and a small frontend stylesheet.

DEMO VEDIO URL:
https://www.loom.com/share/4686ed0c399847b9955e86fc45d95e6c   
