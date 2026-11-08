# Seller feedback, account and catalog sync

Products still require Admin product approval. A new product or full content edit is pending_review until Admin approves it again. Only approved, active products with stock greater than zero appear to buyers. Quick stock updates keep their existing approval.

## Run both projects against the same backend

The web project's local environment uses http://localhost:8000. Mobile defaults to https://shop-hop.site/api/mobile, so local web changes do not reach an app using that default.

For an Android emulator, run from the mobile project:

```powershell
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/mobile
```

For a physical phone, use the development PC's LAN IP instead of 10.0.2.2 and serve Laravel on 0.0.0.0. Both devices need access to the same network/backend. Rebuild/relaunch the app after changing a dart-define.

Apply the additive seller-reply migration to the target backend before opening reply forms:

```powershell
php artisan migrate --path=database/migrations/2026_11_08_000001_add_seller_replies_to_reviews.php
```

## Behavior and manual verification

1. Open Seller, Admin and Buyer sessions on the same backend.
2. Create a product with stock. Confirm it is pending review and hidden from Buyer.
3. Approve it in Admin > Seller Compliance. An idle visible Buyer web page reloads when its catalog digest changes; mobile catalog/home/detail refresh in the foreground every 15 seconds.
4. Edit the product on Seller. Confirm it becomes hidden until Admin reapproves it. Check the updated name, price and media on Buyer after reapproval.
5. Change stock to zero or archive a product. Confirm Buyer removes it; restore active status/stock to show an approved listing again.
6. Open Customer Feedback. Reviews are loaded only for the current seller's products. Save a reply, reopen the page, and check the reply on Buyer product details in web and mobile.
7. Save profile fields, reopen Account Management and confirm persistence. Change password using the current password and matching confirmation. Check invalid phone numbers and incorrect current passwords return errors.

Updates use polling, not WebSockets. Hidden/background screens pause polling. Failed background mobile requests keep the last loaded data. Unsent feedback drafts are preserved. Web catalog refresh pauses after form input or during submission so it cannot discard user work; manually refresh after completing that work.

## Automated checks

From web:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Feature/SellerWorkspaceSyncTest.php tests/Feature/SellerFeedbackJourneyTest.php tests/Feature/SellerAccountJourneyTest.php --do-not-cache-result
node --test tests/js/catalog-refresh.test.cjs
```

From mobile:

```powershell
flutter test
flutter analyze
```

Backend tests use an isolated SQLite :memory: database, not the local MySQL database. Automated HTTP and widget checks do not establish that production has been deployed or that a physical phone has been exercised.
