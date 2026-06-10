# Marketplace / Classificados for phpBB

A phpBB extension that adds a community marketplace/classifieds area to your forum. Members can publish ads with images, price, location, contact options, simple stock quantity, categories, filters and moderation tools.

> This extension is a classifieds/marketplace system. It does **not** include checkout, payment gateways, PayPal integration or order processing.

## Features

### Public marketplace

- Marketplace-style public listing with search, filters and responsive ad cards.
- Category, type, condition, price, image and location filters.
- Modern ad detail page with gallery, thumbnails and main image zoom.
- Multiple images per ad with main image selection.
- Simple stock/quantity support for products with more than one unit.
- Support for unique items, services, wanted ads and repeated-stock products.
- Featured ads, sold/expired/hidden/pending statuses and visual badges.

### Posting and user area

- User-friendly ad creation/edit form.
- Image upload with preview.
- Main image highlight and image ordering.
- Price, currency, location, phone, contact method, condition and ad type fields.
- UCP page for users to manage their own ads.
- Quick stock actions for increasing/decreasing available quantity.
- User notifications for relevant marketplace events.

### Administration and moderation

- ACP dashboard with marketplace statistics and moderation shortcuts.
- ACP ad management with status, reports, stock, price and action controls.
- Category management with per-category rules.
- Support for approval workflow, hidden ads, hidden reason, reports and notifications.
- Category counters for total, active, pending, hidden, sold and expired ads.
- Expiration handling through phpBB cron task.

### Categories and internationalization

- Default categories use language keys, so they can be translated per language pack.
- Custom categories remain editable as normal text.
- Included languages:
  - English
  - Portuguese (Brazil)

## Requirements

- phpBB `>= 3.3.0, < 4.0.0`
- PHP `>= 7.2`
- A database supported by phpBB 3.3
- Writable `files/` directory for uploaded marketplace images

## Installation

1. Download or clone this repository.
2. Copy the extension to:

   ```text
   ext/mundophpbb/marketplace/
   ```

3. Confirm that this file exists:

   ```text
   ext/mundophpbb/marketplace/composer.json
   ```

4. In the ACP, go to:

   ```text
   Customise > Manage extensions
   ```

5. Enable **Marketplace / Classificados**.
6. Clear the phpBB cache.

## Updating

1. Do **not** delete extension data unless you want to remove all marketplace data.
2. Replace the files in:

   ```text
   ext/mundophpbb/marketplace/
   ```

3. Clear the phpBB cache.
4. Visit the ACP so pending migrations can run.

If you are testing locally and the phpBB migrator becomes stuck because of an interrupted development build, clean the affected extension migration records before testing again. Do this only on test installations and only if you know what you are removing.

## Public routes

Depending on your phpBB URL configuration, routes are available under `app.php`, for example:

```text
/app.php/marketplace
/app.php/marketplace/category/{category_id}
/app.php/marketplace/ad/{ad_id}
/app.php/marketplace/post
/app.php/marketplace/image/{image_id}
```

## Permissions

The extension adds marketplace-specific permissions.

### User permissions

- `u_marketplace_view` — view marketplace
- `u_marketplace_post` — post ads
- `u_marketplace_edit_own` — edit own ads
- `u_marketplace_delete_own` — delete own ads
- `u_marketplace_report` — report ads
- `u_marketplace_bump_own` — bump own ads

### Moderator permissions

- `m_marketplace_approve` — approve ads
- `m_marketplace_edit` — edit marketplace ads
- `m_marketplace_delete` — delete marketplace ads
- `m_marketplace_feature` — feature/unfeature ads
- `m_marketplace_reports` — manage reports

Review group permissions after installation to match your community rules.

## Category rules

Categories can define rules such as:

- price required
- location required
- phone required
- images allowed or blocked
- accepted ad types
- expiration period
- approval requirements

These rules are shown in the ACP and can also be surfaced in the posting form to guide users.

## Images

Uploaded images are stored under:

```text
files/marketplace/
```

Images are served through the extension route instead of being linked directly. This keeps image delivery inside phpBB's routing layer.

## Simple stock support

Each ad can have an available quantity.

Examples:

- `1` for a unique used product
- `10` for multiple units of the same product
- `0` for sold out / unavailable

When quantity reaches zero, the ad can be treated as sold or out of stock. This feature is intentionally simple and does not implement cart, checkout, payments or orders.

## Development notes

Relevant directories:

```text
acp/                         ACP module entry files
adm/style/                   ACP templates
config/                      service and route definitions
controller/                  public, UCP and ACP controllers
cron/task/                   expiration cron task
event/                       phpBB event listener
language/en/                 English language files
language/pt_br/              Brazilian Portuguese language files
migrations/                  database schema/data migrations
service/                     marketplace service layer
styles/all/template/         public and UCP templates
styles/all/template/event/   phpBB template events
styles/all/theme/            marketplace CSS
ucp/                         UCP module entry files
```

## Testing checklist

Before publishing a release, test at least:

- fresh installation
- update from the previous version
- disabling the extension
- deleting extension data
- creating an ad without images
- creating an ad with multiple images
- changing the main image
- reordering images
- creating an ad with quantity greater than 1
- reducing quantity to zero
- editing an ad from UCP
- reporting an ad
- moderating reports in ACP
- approving/hiding ads
- category rules in the posting form
- mobile layout
- English and Portuguese language packs

## Version

Current extension version: `1.5.0-rc1`

## License

GPL-2.0-only

## Credits

Developed by **Mundo phpBB** for the phpBB community.

Website: <https://www.mundophpbb.com.br>


## Release Candidate 1.5.0-rc1

This package is a release candidate for Marketplace / Classificados. It consolidates the current feature set and the online migration repair without adding new database changes beyond the existing 1.4.12 migration chain.

### Highlights

- More ads from the same seller.
- Expired ad renewal/republication flow.
- Featured ads and boosted ads.
- Configurable promotion packages.
- PayPal flow for paid promotion packages.
- Direct PayPal purchase flow paid to the seller, not to the forum administrator.
- Seller-side sale confirmation in UCP.
- Follow seller feature with notifications.
- Public, posting and UCP visual refresh.
- Migration chain reviewed for cleaner installation and online repair scenarios.

### Recommended test checklist

- Clean installation.
- Incremental update from the previous package.
- Extension disable/enable cycle.
- Data purge test on a non-production copy.
- Public listing, ad view and ad posting form.
- UCP ad management and seller confirmation.
- ACP category, settings, packages and pending requests.
- PayPal sandbox for promotion payments and seller direct payments.

