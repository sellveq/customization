# Changelog

## 3.0.0

Forked from `scandipwa/customization` 2.0.4. Module name and namespace are unchanged, and the package replaces `scandipwa/customization` at every version, so it installs as a drop-in replacement.

- The favicon upload goes through Magento's own backend model again: a file whose extension is not `png` is refused, an image chosen from the media gallery is found where it actually is, and a rejected upload leaves nothing behind.
- A favicon that is not square is refused with a message naming the size it has, instead of being squashed into every icon.
- Uploading a favicon rewrites `media/webmanifest/manifest.json` as well as the icons, so the manifest stops advertising an empty icon list once a favicon is set.
- Saving the design configuration without choosing a new favicon no longer rebuilds the icons, and a store whose icons were never generated gets them on the next save of any design field.
- The web manifest and the page head advertise only the icons that exist, instead of 19 manifest icons and 31 `<link>` tags whether or not a favicon was ever saved.
- No icon is published as `maskable` any more: the generated icons are plain resizes with no safe zone, and Android cropped the one that claimed it.
- App icon links in the page head are built from the store's media base URL, so they survive a media CDN or a subdirectory install.
- The icon list is read fresh after a build, so a request that had already listed the icon directory no longer publishes an empty list.
- The web manifest is written by one method, so the setup patch, the configuration observer and the favicon upload cannot drift apart.
- The manifest is generated at `setup:upgrade` by a data patch, replacing an `InstallData` script Magento never ran because the module declares no `setup_version`.
- A failed manifest write is logged instead of discarded, so `setup:upgrade` no longer reports success over a manifest it did not write.
- A configuration value the manifest cannot encode now raises instead of being read as "nothing to write".
- The admin colour fields refuse a value that is not 6 or 8 hexadecimal digits, which is what the storefront needs to emit a valid colour.
- The colour fields use a `spectrum` picker with an alpha channel, replacing the jQuery colorpicker the module loaded through its own admin layout file.
- The module adds an admin menu group, Configuration under the Scandiweb menu, holding a link to each of its four configuration sections, beside the Stores > Configuration route.
- The configuration tab is labelled `ScandiPWA`, not `Scandipwa`.
- The favicon field's notice describes the requirement — a square PNG, from which every app icon size is generated — instead of claiming the file has to be named `favicon`.
- The web manifest's `description` no longer defaults to `ScandiPWA theme DEMO store`; a store that sets none gets a manifest without the member.
- The default `start_url` of a new install is `/` instead of a URL carrying upstream's analytics campaign parameters.
- The admin datepicker patch is removed; Magento 2.4.9 positions datepickers inside modal panels itself.
- Four field comments in Content customization say "below" instead of "bellow".
- Requires PHP 8.3, declares every Magento requirement at the 2.4 line, declares `magento/module-backend`, `magento/module-media-storage`, `magento/module-theme`, `magento/module-ui` and `selveq/module-core` and mirrors them in `<sequence>`, replaces `scandipwa/customization` at every version, and excludes `/.github` and `/.gitignore` from the package archive.
