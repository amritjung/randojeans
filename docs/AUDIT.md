# Rando Jeans — Codebase Audit

**Date:** 2026-11-17 · **Branch:** `feature/ecc-setup` · **Scope:** full codebase, read-only. Nothing was modified except this file.

**Legend.** **CONFIRMED** means the code path was traced or a check was run. **SUSPECTED** means it is plausible from the code but needs a manual or runtime test. Paths starting with `P/` are short for `resources/views/pages/⚡`.

---

## 1. Automated checks (Phase 1)

| Check | Result |
|---|---|
| `php artisan about` | Laravel 13.24.0, PHP 8.4.23, Filament 5.7.6, Livewire 4.3.5. Locally: env=local, **debug ENABLED**, config, routes and events not cached, `public/storage` linked. Drivers: queue=database, session=database, cache=database, mail=smtp. |
| `php artisan route:list` | 24 app routes plus 33 Livewire page routes. `POST stripe/webhook` is CSRF-exempt in `bootstrap/app.php:15`. No route uses the `verified` middleware. |
| `php artisan test --compact` | **FAIL, 1 of 2.** `Tests\Feature\ExampleTest` expected 200 on `/` and got **404**. Cause: `P/home.blade.php:14` uses `Product::where('status','active')->firstOrFail()`, so the homepage 404s whenever no product is active. The only other test is the stock `Unit\ExampleTest`. **The app has effectively no test coverage.** |
| `composer audit` | **26 advisories in 11 packages.** These include `laravel/framework` (1 critical: TokenGuard bypass, not exploitable here because there are no API tokens; 1 medium: relationship-inclusion data destruction; 2 low), `symfony/yaml` (high), `league/commonmark` (4 high DoS, 1 medium), `filament/filament` (medium: MFA management without re-auth). |
| `npm audit` | **4 advisories: 2 critical, 2 high.** `shell-quote` via `concurrently` is critical, and `vite` 8.2.0–8.2.2 and `source-map-js` are high. All are dev-time tooling, not shipped to browsers. `npm audit fix` is available. |
| Static analysis | Larastan 3.10 is installed, and `phpstan.neon` sets level 7. I ran `phpstan analyse app --level=5`: **20 errors.** Real ones: `StripeWebhookController.php:38` (`payment_intent` on a generic `StripeObject`), `Discount.php:36` and `Order.php:100` (Carbon methods on columns phpstan types as `string`), `Product::primaryImage()` return type. Several are Filament false positives (`$form`, `lightbox()` macro). |
| `php -l` (app, routes, config, database, views, plus the PHP blocks of every ⚡ component) | **0 syntax errors.** `php artisan view:cache` compiles cleanly. |
| Pint | Not run, as instructed. The code-review agent ran `pint --test`, which **fails on about 60 files** (imports order, EOF newline, brace position, indentation). |

---

## 2. Findings (severity-ranked)

| ID | Sev | Status | Location | Issue | Evidence | Suggested fix | Effort |
|---|---|---|---|---|---|---|---|
| **C1** | Critical | CONFIRMED | `StripeWebhookController.php:35-41`, `P/product.blade.php:103-113`, `P/cart.blade.php:20-29`, `P/checkout.blade.php:259-269`, `ReturnRequestsTable.php:136` | **Stock is never decremented when an order is paid, and quantity is never checked against stock.** The only stock check is `stock_quantity > 0` at add-to-cart. The refund action *increments* stock, so stock only ever goes up. | `grep decrement` returns nothing. `addToCart` adds `$this->quantity` with no cap. `updateQuantity` has no maximum. `submit()` never re-checks stock. | In the webhook, inside `DB::transaction` with `ProductVariant::lockForUpdate()`, decrement each item's stock (guarded by `where('stock_quantity','>=',qty)`). Validate `qty <= stock` at add-to-cart, cart update and checkout submit. Decide what to do with an oversold paid order (flag it for admin). | M |
| **H1** | High | CONFIRMED | `P/login.blade.php:46-50`, `P/register.blade.php:97-101`, `CartItem.php:15` | **The guest cart is lost on login or register.** `session()->regenerate()` runs before the cart rows are re-tagged, so `where('session_id', session()->getId())` uses the *new* ID and matches nothing. Carts are also only ever read by `session_id`, never by `user_id`, so a user's cart doesn't follow them across sessions or devices either. | Line order: regenerate, then `CartItem::where('session_id', session()->getId())->whereNull('user_id')->update(...)` | Capture `$oldId = session()->getId()` before regenerate and update rows `where('session_id',$oldId)` to the new session ID and user ID. Make `currentItems()` read `user_id` when logged in, else `session_id`. Merge duplicate variants. | S |
| **H2** | High | CONFIRMED | `P/request-return.blade.php:35-62` | **A return request accepts any `order_item_id` and any quantity.** `$quantities` is a client-writable public array. Its keys go straight into `ReturnRequestItem::create`, with no check that the item belongs to this order and no `qty <= purchased` check. The admin refund then pays `unit_price * qty`, which can be up to the full charge, and restocks the wrong variants. The admin's approval is the only safeguard. | `'order_item_id' => $orderItemId` with no ownership or quantity validation | Validate each key against `$this->order->items->pluck('id')` and `integer|min:0|max:{item.quantity}`. Drop unknown keys. | S |
| **H3** | High | CONFIRMED | `ReturnRequestsTable.php:115-122` | **The refund ignores the order discount, so discounted orders get over-refunded.** The refund uses the pre-discount `unit_price`. Example: on a 10%-off order, returning a $100 item refunds $100 for $90 paid. The amount also isn't capped at the total minus earlier refunds. | `$refundAmount = sum(unit_price * quantity)` | Pro-rate: `unit_price * qty * (1 - discount_amount/subtotal)`. Cap at the remaining refundable amount. Round once. | S |
| **H4** | High | CONFIRMED | `ReturnRequestsTable.php:119-150` | **A refund can succeed in Stripe while the admin is told it failed.** `Refund::create` runs before `update` and `Mail::send`, inside a `catch (\Exception)`. If SMTP throws, the admin sees "Refund failed" although money was refunded. There is no Stripe idempotency key. | `try { Refund::create(...); $record->update(...); ...; Mail::send(...) } catch (\Exception $e) { 'Refund failed' }` | Pass `idempotency_key: 'refund-rr-'.$record->id`. Persist the refund ID before anything else. Queue the email outside the try. Use `report($e)`. | S |
| **H5** | High | CONFIRMED | `Order.php:76-85`, `ReturnRequest.php:55-74`, `StripeWebhookController.php:36`, all `app/Mail/*` | **Every email is sent synchronously from Eloquent `updated`/`created` events, and no Mailable implements `ShouldQueue`.** If SMTP fails in the webhook, the order is already `paid` and the webhook returns 500. Stripe retries, sees `status !== 'pending'` and skips, so **the customer never receives a confirmation.** In admin, the status saves, then a 500 is shown, and re-saving doesn't resend. The invoice PDF also renders inline during the request. The queue worker (`QUEUE_CONNECTION=database`) is unused. | `'paid' => Mail::to(...)->send(new OrderConfirmed($order))` | Make the Mailables `ShouldQueue` and use `->afterCommit()`. Run `queue:work` in production. Optionally move the sends to listeners or observers. | M |
| **H6** | High | CONFIRMED | `P/checkout.blade.php:215-322` | **If Stripe fails after the Order row is created, the system is left inconsistent.** The Order, OrderItems and DiscountUsage are written first, with no transaction, and then `Coupon::create` and `Session::create` are called with no try/catch. A Stripe error leaves an orphan `pending` order and **consumes the customer's single-use discount**, and the user sees a 500 page. Pending orders are never cleaned up because `checkout.session.expired` is unhandled. | No `DB::transaction` and no try/catch around the Stripe calls | Write the order rows in a transaction. Wrap the Stripe calls. On failure, mark the order `failed`, delete the DiscountUsage and show a friendly error. Handle `checkout.session.expired` (cancel the order, release the discount). Add a scheduled prune for stale pending orders. | M |
| **H7** | High | CONFIRMED (exploit needs a crafted Livewire request) | `P/product.blade.php:14,112`, `P/cart.blade.php:20-29` | **Cart quantity is client-writable and unvalidated.** `public int $quantity` has no `#[Locked]` and no validation, so a negative or huge value goes into `cart_items`. A negative line gives a negative subtotal, so a wrong `orders.total` is persisted. Stripe rejects quantity < 1, which then triggers H6. | `$item->quantity = existing + $this->quantity` | `validate(['quantity' => 'integer|min:1|max:10'])` and clamp to stock (C1). Add a max to `updateQuantity`. | S |
| **H8** | High | CONFIRMED | `composer.lock`, `package-lock.json` | **Dependency advisories:** 26 Composer advisories (framework critical/medium, commonmark high, yaml high, Filament MFA medium) and 4 npm advisories (2 critical, dev-only). | `composer audit`, `npm audit` | `composer update laravel/framework filament/filament league/commonmark symfony/yaml` and `npm audit fix`, then run the test suite. Needs approval per AGENTS.md. | S |
| **H9** | High | CONFIRMED | `tests/` | **There are effectively no tests.** The one feature test fails, and nothing covers money, stock, discounts, the webhook, refunds or authorization. | 2 tests, both stock examples | See §5 for the highest-value tests. | L |
| **M1** | Medium | CONFIRMED | `P/home.blade.php:14` | **The homepage 404s when there are no active products**, for example if every product is archived in production, or on a fresh database. This is why the test suite fails. | `->firstOrFail()` | Use `->first()` and render an empty state. | S |
| **M2** | Medium | CONFIRMED | `P/checkout.blade.php:131-152, 241-257`, `Discount.php:27` | **Several gaps in discount handling:** (a) Validity, `max_uses` and per-email use are checked only in `applyDiscount`, not again in `submit()`, so two tabs or an expiry in between both succeed (the code comment claims it closes this exploit, but it doesn't). (b) Changing the email after applying the code skips the per-email check. (c) Usages from abandoned orders count toward `max_uses`. (d) "One per email" is trivially bypassed with `a+1@…` aliases. | No re-check in `submit()` | Re-validate the discount and email inside the submit transaction with `lockForUpdate`. Add a unique `(discount_id, email)`. Count only usages whose order is paid toward `max_uses`, or release them on expiry or failure (H6). | M |
| **M3** | Medium | CONFIRMED | `P/checkout-success.blade.php:13-17` | **The success page shows any order by order number.** It renders the first name, full email and total to anyone with the URL, with no check of `session_id` or ownership. It also **clears the cart whenever the page is visited**, paid or not. Carts of users who pay but never come back to this page are never cleared, because the webhook doesn't clear them. | `Order::where('order_number', request()->query('order'))->first()` | Look the order up via the `session_id` query parameter matched to `stripe_checkout_session_id`, or store the order ID in the session at checkout. Clear the cart in the webhook, and record `session_id`/`user_id` on the order to do so. Mask the email. | S |
| **M4** | Medium | CONFIRMED | `routes/web.php:9`, `P/product.blade.php:29,101`, `P/checkout.blade.php` | **Draft or archived products can be viewed and bought by slug.** Neither the product page nor checkout checks `isActive()`. | `mount(Product $product)` has no status check | `abort_unless($product->isActive(), 404)`. Filter inactive products in `currentItems()` and at checkout. | S |
| **M5** | Medium | CONFIRMED | `StripeWebhookController.php:15-44` | **Webhook gaps:** (a) Only `SignatureVerificationException` is caught; a malformed payload (`UnexpectedValueException`) causes a 500. (b) No `payment_status === 'paid'` check. (c) No `amount_total` vs `order->total` check. (d) The pending→paid change isn't locked, so concurrent deliveries can double-email. (e) Only `checkout.session.completed` is handled (no expired, refund or dispute events). (f) `stripe_checkout_session_id` has no index. | See file | Wrap the handler in a transaction with `lockForUpdate`. Assert the payment status and amount. Catch `UnexpectedValueException`, returning 400. Add the event handlers. Add a unique index on the session ID. | M |
| **M6** | Medium | CONFIRMED | `P/request-return.blade.php:26,35` | **The same order can get several return requests.** `isReturnEligible()` (one request per order, 30-day window) runs only in `mount()`, so a second tab or a replayed `submit()` creates duplicates. | No eligibility check in `submit()` | Re-check in `submit()` inside a transaction. Add a unique index on `return_requests.order_id`. | S |
| **M7** | Medium | CONFIRMED | `P/product.blade.php:17-18,125-147` | **Reviews can be forged.** `submitReview()` never re-checks eligibility or authentication, and `reviewableOrderItemId` isn't `#[Locked]`. A logged-in non-buyer can set it to any order-item ID. (Guests hit a NOT NULL error, which shows a 500.) Reviews are moderated, but every submission emails all admins synchronously, with no throttle. | `'order_item_id' => $this->reviewableOrderItemId` | Add `#[Locked]`. In the action, `abort_unless(auth()->check())` and recompute `reviewableOrderItemFor(auth()->id())`. Add a throttle. Queue the email. | S |
| **M8** | Medium | CONFIRMED | `Order.php:15`, `SendReviewReminders.php:22,28` | **The review reminder's "already sent" flag is never saved.** `review_reminder_sent_at` isn't in `$fillable`, so `update()` silently drops it, and running the command twice sends duplicates. The exact-date `whereDate(..., now()->subDays(7))` window also means a day with the scheduler down skips those customers for good. There is no per-order try/catch. | `$order->update(['review_reminder_sent_at' => now()])`; the column exists in the migration but not in fillable | Add the field to fillable and casts. Use `delivered_at <= now()->subDays(7)` with `whereNull(sent_at)`, `chunkById`, and a try/catch per order. Add `withoutOverlapping()->onOneServer()`. | S |
| **M9** | Medium | CONFIRMED | `P/product.blade.php:9` | **The product page title is hardcoded** to "The Original Black Jean" for every product. | `#[Title('The Original Black Jean')]` | Set the title dynamically in `render()`/`mount()` (`->title($product->name.' — Rando Jeans')`). | S |
| **M10** | Medium | CONFIRMED | `layouts/app.blade.php:5-10`, `public/robots.txt` | **SEO basics are missing:** no meta description, canonical, OpenGraph/Twitter tags or Product/Offer/AggregateRating JSON-LD. `robots.txt` allows everything (cart, checkout, account, admin) and lists no sitemap, and there is no sitemap. | Only `<title>` in head | Add head slots or `@stack('head')` in the layout. Emit JSON-LD on the product page. Disallow private paths. Add a sitemap route (no new dependency needed). | M |
| **M11** | Medium | CONFIRMED | `P/forgot-password.blade.php:11`, `P/verify-email.blade.php:15`, `P/account-delete.blade.php:19`, `P/reset-password`, `P/product.blade.php` (review, add-to-cart), `config/livewire.php` | **Gaps in rate limiting.** Throttled today: login (5/min, email+IP), register (8/h/IP), discount (10/10min/IP), checkout (8/10min/IP plus a lock), and the verify link (6/min). **Not throttled:** forgot-password, verification resend, reset submit, account-delete requests (each one emails every admin), review submit (also emails admins), add-to-cart/cart updates, and wishlist. | No `RateLimiter` in those files | Add a `RateLimiter` keyed on `user|ip` to each action. | S |
| **M12** | Medium | SUSPECTED | `bootstrap/app.php` | **IP-keyed rate limits may break in production.** All limits key on `request()->ip()`, and `trustProxies` isn't configured. Behind a load balancer or CDN, all customers share one IP, so limits would lock everyone out at once (or be useless). | No `trustProxies` | Configure `$middleware->trustProxies(at: ...)` for the hosting platform. | S |
| **M13** | Medium | CONFIRMED | `ImagesRelationManager.php:32`, `app/Filament/Pages/ManageHomepage.php` | **Uploads are under-validated.** `image/*` accepts SVG, which is served from the app origin via `/storage`, so it is a stored XSS vector. There's no `maxSize()` anywhere, so video is unbounded (and autoplays on the homepage). Files are orphaned when a ProductImage or Product is deleted. | `acceptedFileTypes(['image/*', 'video/mp4', 'video/quicktime'])` | Use an explicit list (jpeg/png/webp/mp4) and `maxSize()`. Delete files in a `ProductImage::deleting` hook. | S |
| **M14** | Medium | CONFIRMED | `create_order_items_table.php:17`, `create_product_variants_table.php:16` | **Deleting a product or variant that has been ordered gives an admin 500.** `order_items.product_variant_id` restricts deletes, while variants cascade from products, so the delete fails with a foreign-key error. | `foreignId('product_variant_id')->constrained()` | Archive instead of deleting: hide the delete action for ordered products, or use SoftDeletes on Product/Variant. | M |
| **M15** | Medium | CONFIRMED | `⚡cart-badge.blade.php:18`, `layouts/app.blade.php:55,67`, `Product.php:80-89`, `P/home.blade.php:13`, `P/product.blade.php:43,574-629`, `CartItem.php:16`, `SiteSetting.php:20`, `ProductsTable.php:24` | **Performance and N+1 queries:** (a) The cart badge loads full cart, variant and product models just to sum the quantity, twice per page (desktop and mobile instances). (b) `reviewCount()`/`averageRating()` run a query on every call, about 5 times on the product page and about 9 on home. (c) Images aren't eager-loaded for home, related products, cart rows or the Filament products table. (d) `SiteSetting::current()` hits the database on every page. | Traced by the code-review agent and verified | Sum the quantity in SQL (`sum('quantity')`). Use `withCount`/`withAvg` once. Add `with('images')` and `with('productVariant.product.images')`. Cache SiteSetting and clear it on save. | S |
| **M16** | Medium | CONFIRMED | `P/product.blade.php:181,240-300`, `layouts/app.blade.php:73`, `P/checkout.blade.php:341-378` | **Accessibility gaps:** the lightbox opens only on `@dblclick` (not usable from a keyboard or by touch-discovery) and has no `role="dialog"`/`aria-modal`, focus trap, focus return or close label. The mobile menu has no `aria-expanded`/Escape. Checkout and other form `<label>`s aren't tied to inputs (no `for`/`id`). The toast has no `aria-live`. There's no skip link. | See lines | Use `x-trap.noscroll`, ARIA attributes and a labelled close button. Make the trigger a real button. Add `for`/`id` pairs. | M |
| **M17** | Medium | CONFIRMED | `config/session.php:172`, `.env.example:4,21,32,50`, no headers middleware | **Production-hardening gaps:** `SESSION_SECURE_COOKIE` has no default, and `.env.example` ships with `APP_DEBUG=true`, `LOG_LEVEL=debug` and `MAIL_MAILER=log`. There are no security headers (CSP, HSTS, X-Frame-Options, nosniff, Referrer-Policy). | See config | Use the production env checklist in §6 and add a small security-headers middleware. | S |
| **M18** | Medium | CONFIRMED | `routes/web.php`, `P/checkout.blade.php:46` | **Email verification is only enforced at checkout.** No route uses `verified`, and checkout checks `hasVerifiedEmail()` only in `mount()`, not in `submit()`. | No `verified` middleware | Add `verified` to the account, wishlist and returns routes, and re-check in `submit()`. | S |
| **L1** | Low | CONFIRMED | `AdminForm.php:35`, `User.php:35`, `ReturnRequestForm.php:40` | **Admin permissions are flat.** Any admin can create, demote or reset any admin; there are no policies or super-admin role, and no 2FA. `is_admin` and `email_verified_at` are mass-assignable (no public path abuses this today). Admins can also set a return to "Refunded" by hand, which skips the Stripe refund. | `Toggle::make('is_admin')`; `'refunded' => 'Refunded'` | Remove those fields from `#[Fillable]` and use `forceFill`. Add a policy or super-admin flag. Make "refunded" reachable only through the action. Enable Filament MFA. | M |
| **L2** | Low | CONFIRMED | Migrations | **Missing unique constraints:** `cart_items (session_id, product_variant_id)` (duplicate rows under a race in `firstOrNew`), `discount_usages (discount_id, email)`, `return_requests.order_id`. Status columns aren't indexed. | grep of migrations | Add these in one migration. | S |
| **L3** | Low | CONFIRMED | `Order.php:23`, `OrderItem.php`, `ReturnRequest.php:24`, `P/checkout.blade.php:62-93` | **Money columns have no casts and totals use PHP floats.** Today's rounding is consistent (Stripe and server totals agree for 2-dp prices), but it's fragile. | No `decimal:2` casts on `subtotal`/`total`/`gst`/`unit_price`/`refund_amount` | Add the casts. Compute totals in integer cents. | M |
| **L4** | Low | CONFIRMED | `P/checkout.blade.php:199`, `P/register.blade.php` | **The name regex rejects valid names.** `^[A-Za-z'\-]{2,}(\s…)+$` rejects accented letters (José, Zoë), non-Latin scripts, single names, and two-letter parts like "Li". | regex | Use `/^[\pL\pM'\- .]{2,}$/u` or just `required|max`. | S |
| **L5** | Low | CONFIRMED | `P/checkout.blade.php:81` | **Free shipping uses the pre-discount subtotal**, so $150 minus 10% ($135) still ships free. This may be intended; it needs a business decision. | `$this->subtotal >= 150` | Decide, and if needed use `subtotal - discountAmount`. Move 150 and 12.00 to config constants. | S |
| **L6** | Low | CONFIRMED | `P/checkout.blade.php:308` | **A one-off Stripe Coupon is created on every discounted checkout attempt** and never deleted, which clutters the Stripe dashboard. | `Coupon::create(...)` per submit | Delete it after the session completes or expires, or create one Stripe coupon or promotion code per Discount. | S |
| **L7** | Low | CONFIRMED | `P/product.blade.php:438,472,531`, `components/product/custom-sections.blade.php` | **Admin rich text is rendered raw with `{!! !!}`.** It's admin-authored (Filament RichEditor), but anything written outside the editor is trusted. | `{!! $product->description_body !!}` | Pass it through `Str::sanitizeHtml()` or Filament's rich-content renderer. | S |
| **L8** | Low | CONFIRMED | `P/wishlist.blade.php:40` | **`updateVariant` doesn't check that the variant belongs to the item's product.** It's scoped to the user, so impact is low. | No product check | Use `ProductVariant::where('product_id', $item->product_id)->findOrFail($variantId)`. | S |
| **L9** | Low | CONFIRMED | `P/security.blade.php`, `P/reset-password`, account soft-delete | **Password change or reset doesn't end other sessions**, and a soft-deleted user's database sessions stay alive. | — | `Auth::logoutOtherDevices()` and delete session rows. | S |
| **L10** | Low | CONFIRMED | `P/shop.blade.php:281` | **Shop images use `primaryImage()->alt_text`, which can be null**, giving an empty alt. No images have width/height (layout-shift risk). The homepage hero has no `fetchpriority`, and the video has no poster or `preload`. | — | Fall back to the product name. Add dimensions, lazy loading and a poster. | S |
| **L11** | Low | SUSPECTED | `text-neutral-500/600/700` on `bg-ink` | **Contrast is likely below 4.5:1** for the small mono text and disabled states. | Needs measurement | Lighten the tokens. | S |
| **L12** | Low | CONFIRMED | ~60 files; `welcome.blade.php`; inline FQCNs; `routes/web.php` indentation | **Code-hygiene issues:** Pint fails, `welcome.blade.php` is dead code, there are inline `\App\Models\…` FQCNs, missing return types (`currentItems()`, `approvedReviews()`), and long narrative inline comments (Boost prefers PHPDoc). | `pint --test` | Run `vendor/bin/pint` on its own branch and remove the dead code. | S |
| **L13** | Low | CONFIRMED | `P/register.blade.php` | **Registration reveals whether an email exists** (`unique:users,email`). It's throttled at 8/h. | — | Accept as a trade-off or use a generic message. | S |

**Checked and found OK:**
- GST is computed as `(discounted subtotal + shipping) / 11`, which is correct for GST-inclusive AUD.
- Stripe line items, shipping and the coupon add up to the server total.
- The webhook verifies the signature, and its CSRF exemption is scoped to the webhook only.
- Order, return and order-detail pages check ownership (`abort_unless($order->user_id === auth()->id())`).
- Cart actions are scoped to `session_id`, and wishlist actions to the user.
- Profile, address and security forms use explicit field arrays, and the password change requires the current password.
- Login is throttled per email+IP, and logout invalidates the session and token.
- The verify link is signed and throttled.
- Forgot-password gives the same response whether or not the email exists.
- The invoice PDF is used only as an email attachment (no download route).
- `config/app.php` defaults `debug` to false.

---

## 3. Top 10 to fix first

1. **C1:** Decrement stock on payment and check stock at add-to-cart, cart update and checkout.
2. **H1:** Fix the cart being lost on login and register (capture the old session ID before regenerate; read the cart by `user_id`).
3. **H2 + M6:** Validate return items and quantities against the order, and re-check eligibility in `submit()`.
4. **H3 + H4:** Make refunds correct: pro-rate the discount, add an idempotency key, and keep a mail failure from reporting a refund as failed.
5. **H5:** Queue all mail (`ShouldQueue` + `afterCommit`) and run a queue worker, so SMTP failures can't lose confirmations or break saves.
6. **H6 + M2:** Make checkout transactional, handle Stripe failures and `checkout.session.expired`, and re-validate the discount at submit.
7. **H7 + M7:** Lock and validate client-writable Livewire properties (`quantity`, `reviewableOrderItemId`, `canReview`).
8. **M5 + M3:** Harden the webhook (lock, payment status, amount, malformed payload), clear the cart from the webhook, and stop the success page exposing orders by number.
9. **H8:** Apply the Composer and npm security updates, after approval, and re-run the tests.
10. **H9 + M1:** Fix the homepage `firstOrFail` and add the core-flow test suite (§5) before or alongside the fixes above.

---

## 4. Proposed fix plan (separate branches)

Each branch is test-first: write the failing Pest test, fix it, run `pint --dirty`, then review. Suggested order:

| # | Branch | Findings | Notes |
|---|---|---|---|
| 1 | `test/core-flows` | H9, M1 | Factories for Product, Variant, CartItem, Order, Discount, ReturnRequest. Many tests start red and turn green in later branches (or go in with each branch). Fixes the homepage 404. |
| 2 | `fix/stock-and-orders` | C1, H6, H7, M4, M5, M14, L2 (cart/session unique) | Transactional checkout, stock decrement in the webhook with a lock, quantity validation, inactive-product guard, webhook hardening plus expired-session handling, archive instead of delete, migration for indexes. |
| 3 | `fix/cart-login` | H1, M3 | Re-tag the cart before regenerate, read the cart by user, merge carts, clear the cart in the webhook, secure the success-page lookup. |
| 4 | `fix/discounts` | M2, L5, L6 | Re-validate at submit, a unique `(discount_id,email)`, release usages on failure or expiry, business decision on the shipping threshold. |
| 5 | `fix/returns-refunds` | H2, H3, H4, M6, L1 (manual "refunded") | Validated return items, pro-rated refunds with idempotency key, unique `order_id`. |
| 6 | `fix/mail-queue` | H5, M8 | `ShouldQueue` + `afterCommit` on every Mailable, reminder fillable/window/overlap, production queue worker. |
| 7 | `fix/auth-hardening` | M7, M11, M12, M18, L1, L8, L9, L13 | Throttles, `#[Locked]` properties, `verified` middleware, trusted proxies, admin policy/MFA, session invalidation. |
| 8 | `fix/uploads` | M13, L7 | MIME allow-list, `maxSize`, file cleanup, HTML sanitizing. |
| 9 | `fix/seo-a11y` | M9, M10, M16, L10, L11 | Dynamic titles, meta/OG/JSON-LD, robots/sitemap, lightbox/menu/form accessibility, image attributes. |
| 10 | `perf/queries` | M15, L3 | Eager loading, `withCount`/`withAvg`, SiteSetting cache, cheap badge count, money casts. |
| 11 | `chore/deps-and-style` | H8, L12, M17 | Composer/npm updates (needs approval), Pint run on its own commit, dead code, security-headers middleware, `.env.example` notes. |

---

## 5. Highest-value missing tests

1. **Cart totals:** subtotal uses `price_override`, then `sale_price`, then `base_price`. Shipping is $12 below $150 and free at or above it.
2. **GST:** `(subtotal − discount + shipping) / 11`, rounded to 2 dp, for a few edge amounts.
3. **Discount maths:** percentage applies to products only. Expired, inactive or max-used codes are rejected. One use per email. **Re-validated at submit.**
4. **Checkout creates the Order and OrderItems with the correct snapshot prices**, and Stripe line items plus the coupon equal `orders.total` (mock the Stripe client).
5. **Stripe failure after order creation** leaves no consumed discount and no 500.
6. **Webhook:** bad signature returns 400. `completed` marks the order paid **once** and decrements stock. Replaying the event is a no-op. An unknown session is logged. A malformed payload returns 400.
7. **Stock:** you can't add or checkout more than is in stock, and paying decrements stock.
8. **Cart survives login and register** and is cleared after payment.
9. **Returns:** you can't include another order's items or more than you bought, only one request per order, and the 30-day window holds.
10. **Refunds:** the amount is pro-rated for the discount, there is no double refund, and a mail failure doesn't report the refund as failed.
11. **Authorization:** another user's order, return or wishlist gives 403. Guests are redirected from account routes. A non-admin can't open `/admin`. A non-buyer can't submit a review.
12. **Reminder command:** sends once and sets `review_reminder_sent_at`. Running it again sends nothing.
13. **Homepage and product page render** with zero or with inactive products (404 for an inactive product page).

---

## 6. Production readiness checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning` (or error), `APP_URL=https://…`
- [ ] `SESSION_SECURE_COOKIE=true`, consider `SESSION_ENCRYPT=true`, and keep `SESSION_SAME_SITE=lax`
- [ ] Force HTTPS (`URL::forceScheme('https')` in production, or the platform's setting), add HSTS, and configure `trustProxies` (M12)
- [ ] Use a real mailer (`MAIL_MAILER` is `log` in `.env.example`) and set `MAIL_FROM_*`
- [ ] **Run a queue worker** (`queue:work` under supervisor, or the platform's worker) once H5 is done
- [ ] **Run the scheduler cron:** `* * * * * php artisan schedule:run` (needed for `reviews:send-reminders`)
- [ ] Deploy with `php artisan optimize` (config, route, event and view caches) and `filament:optimize` / `icons:cache`
- [ ] Run `php artisan storage:link` on the server
- [ ] Back up the database and `storage/app/public` uploads off-box, and test restoring
- [ ] Use error monitoring (Sentry, Nightwatch or Flare) plus log rotation (`LOG_STACK=daily`)
- [ ] **Stripe test-to-live switch:**
  - live `STRIPE_KEY`/`STRIPE_SECRET`
  - create a **live** webhook endpoint for `https://…/stripe/webhook` with `checkout.session.completed` (plus `checkout.session.expired` and `charge.refunded` after M5)
  - set the live `STRIPE_WEBHOOK_SECRET`, and fail at boot if it's missing
  - check the Stripe account's tax and statement descriptor
  - do one real low-value purchase and refund end-to-end
  - confirm `aud` currency
- [ ] The error pages (404/403/419/429/500) exist and the 500 page is self-contained; verify them with `APP_DEBUG=false`
- [ ] Add robots/sitemap (M10) before launch, so the cart, checkout and admin aren't indexed

---

*Generated by a read-only audit: automated checks plus a manual trace of the checkout, webhook, cart, auth, returns and refund flows, with code-review and security-review agent passes whose key claims were spot-verified. No code was changed. Waiting for approval before starting any fix branch.*
