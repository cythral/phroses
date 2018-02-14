# CHANGE LOG

## v0.7.0
### New Features
- **Fix Incomplete Redirects**: you can now fix incomplete redirects on-page.  A more detailed error is displayed as well.
- **Configurable Admin URI**: you can now configure /admin and subpages to have a different base uri
- **Site Maintenance Mode**: you can now turn on maintenance mode for specific sites rather than all of them
### Bug Fixes & Minor Changes
- Fixes an overflow issue on PST icons (issue #7)
- Fixes an issue where iOS still had a border and radius on inputs (issue #6)
- JavaScript for the uploads feature is now in its own file
- Upload progress is now more accurate
- Naming upload files is now required
- Upload file sizes are checked before uploading instead of waiting for the server to give the error
- Fixes an issue where you could rename an upload to an existing filename (issue #8)
- New page object replaces the SITE["PAGE"] constant
- The error that displays if no site was detected and the **expose** config in phroses.conf equals false was redone.
- The error that dislays if the default theme was not detected was redone
- THEME response was removed
- ASSET response was added, switches handling from PAGE[404].  Theme assets are also accessible when maintenance mode is turned on.
- Improvements to session handling
- Parser class was removed in favor of zbateson/MailMimeParser

<br />

---

<br />

## v0.6.1
- fixes an issue where css dependencies (cythral/icons, oxygen font) could be loaded over http when on https.  

## v0.6.0
### New Features
- **Uploads**: you can now upload images and other files from the admin panel at /admin/uploads
- **JSON Mode**: page information can now be viewed in the json format if you set mode to json in the query string.  Please note that this only works for public and non-redirect pages.
### Bug Fixes & Minor Changes
- PST / page ui is now loaded through javascript instead of each page being reparsed by the Theme object (fixes issue #5)
- The JavaScript file went through some restructuring / cleanup.
- Better error handling
- Buttons/Links added to /admin for /admin/update and /admin/uploads
- Internal stylesheets are now reloaded on page edit saves
- Sysforms are now gone, Phroses.formify replaced them
- Password fields are now cleared after a successful change on /admin/creds
- Redesigned the admin dashboard and pages for a more streamlined look
- Removed most icons, uses a combination of fontawesome and cythral/icons instead.  Please note that the automatic inclusion of the font awesome stylesheet will be removed in future versions.
- URL in the dashbar links back to the homepage now.
- The dashboard now reloads after a theme change.







