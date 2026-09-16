# ScandiPWA Customization

Fork of [scandipwa/customization](https://github.com/scandipwa/customization) 2.0.4, maintained by Selveq for Magento 2.4.9 and PHP 8.3. Module name and namespace are unchanged, and the package replaces `scandipwa/customization` at every version, so it installs as a drop-in replacement. Selveq is not affiliated with or endorsed by Scandiweb.

## What it does

- Adds a ScandiPWA tab to Stores > Configuration holding four sections: theme colours, CMS block placements, layout direction and the web manifest.
- Generates the app icons and `media/webmanifest/manifest.json` from the square PNG favicon uploaded in the design configuration; every declared size is produced, upscaling a small source, and a store whose icons were never generated gets them on its next design save.
- Feeds the storefront root template through a page result preferred over ScandiPWA Locale's: `getThemeConfiguration()` for the colours, the CMS blocks, the head title and description, the theme colour and the layout direction, plus `getAppIconData()`, `getStoreListJson()`, `getStoreCurrency()` and `getWebsiteCode()`.
- Shows the installed ScandiPWA theme version beside Magento's own in the admin footer.

## Install

```sh
composer require selveq/customization
bin/magento setup:upgrade
```

Upload a square PNG favicon under Content > Design > Configuration to generate the app icons; that save also rewrites the manifest, as do `setup:upgrade` and every save of the Webmanifest customization section.

## Configuration

| Section                                                        | What its fields set                                                                                                                                                                                               |
| -------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Stores > Configuration > ScandiPWA > Color customization       | Switches custom colours on and holds the three primary and three secondary hex values the storefront imports as CSS custom properties.                                                                            |
| Stores > Configuration > ScandiPWA > Content customization     | Picks the CMS block shown in the footer, the mini-cart, the cart, both checkout steps, the header and the Contact Us page, the attribute shown on a product card, and the cookie popup's text and read-more link. |
| Stores > Configuration > ScandiPWA > Webmanifest customization | Fills the web manifest's name, short name, description, language, theme and background colours, start URL, orientation, display mode, IARC rating id and scope.                                                   |
| Stores > Configuration > ScandiPWA > Layout direction          | Sets the storefront's text direction, `ltr` or `rtl`.                                                                                                                                                             |

## License

[OSL-3.0](LICENSE), the license of the original work. Scandiweb's copyright notices are kept in every file, and each file Selveq changed carries a `Modifications © Selveq` notice.
