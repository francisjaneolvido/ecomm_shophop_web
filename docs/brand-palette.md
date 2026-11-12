# ShopHop brand palette

ShopHop uses one light appearance across the public marketplace, authentication and registration flows, Logistics public site, Admin, Seller, Main Logistics, Province Hub, Station Branch and Flutter role screens.

| Purpose | Value |
| --- | --- |
| Primary / primary hover | #10193C / #26305A |
| Accent / accent hover | #EDA3AE / #E1909E |
| Page / surface | #FBF6EE / #FFFFFF |
| Muted text | #6B6B7B |
| Border | rgba(16,25,60,0.15) |
| Success / danger / warning | #1F9D55 / #D93F4C / #F59E0B |

Use white text on navy actions, and navy text on pink controls. Success, danger and warning use their own semantic colors and readable darker text shades.

Web tokens and compatibility styles live in public/css/shophop-brand.css, loaded last using partials.brand-assets. Tailwind source tokens live in resources/css/app.css. Legacy teal/mint class names remain compatibility aliases; new code should use named navy/pink tokens or console components.

Flutter tokens live in lib/core/theme/app_colors.dart. AppTheme.light supplies shared light components and MaterialApp explicitly uses ThemeMode.light. Android night resources use the light window appearance and iOS declares UIUserInterfaceStyle=Light.

The artwork supplied by the user is public/images/logo-bunny.png; the same file is copied to Flutter assets/images/logo-bunny.png. The existing PNG is used unchanged. Brand wordmarks use navy Shop (white on navy surfaces) and pink Hop.

Dark/OLED controls, their global JavaScript API, storage listeners and conditional styles have been retired. The head clears the former saved preference and retains only a fixed light marker for older cached styles. There is no theme preference or automatic appearance switching.

After this update, refresh web pages and restart the Flutter app so the new image asset is bundled.
