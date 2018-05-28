# CHANGE LOG

## v0.9.0
### New Features
- **Metadata Screen**: You can now control page title, type, visibility, and URI from the new metadata screen.
- **IP Subnet Restiction**: you can now restrict admin access to subnets using cidr notation in the admin dashboard.  
- **Screen Shortcuts**: quickly access screens using shortcuts.  Alt-m for metadata, Alt-e for editing, and Alt-d for deletion


### Bug Fixes & Minor Changes
- Fixes an issue where elements in the dashbar were overflowing on smaller screen sizes (issue #10)
- Fixes an issue where global maintenance mode would not go into effect.  Global maintenance mode can now be bypassed if you are logged in.  If you wish to turn off phroses completely, simply disable the conf and restart apache, or stop apache altogether.

<br />

---

<br />

## v0.8.3
 This release fixes an issue with the order by clause in select queries.

## v0.8.2
This release fixes an issue with the typelist template filter.

## v0.8.1
This release fixes an issue where page specific css was not being saved properly.

## v0.8.0
### New Features
 - **Page CSS**: the ability to add custom css to specific pages has been added, and the editor has been completely redone to accommodate this. Page styles are reloaded on keyup, so that you can view your changes in real-time.
 - **IP Restriction**: you can now restrict administrator access to specific ip addresses on /admin
### New Commands
 - **version**: displays the current version of phroses that is in use
 - **reset**: easily reset the database back to default
 - **restore**: restores the database from input sql (pipe a backup created from mysqldump)
### Bug Fixes & Minor Changes
 - Refactored routing.  Introduced a Route and Cascade class to handle routing and cascading route rules. Route controlling now handled by a separate class rather than the front controller.
 - Refactored installation, installation functions have been moved to a class.
 - The Theme class uses a 'Loader' to load errors, assets and types.  DummyLoader and FolderLoader are the current ones, PharLoader to be added in a future version.
 - New Site class replaces the SITE[] constant.
 - \Phroses\Database\Database replaces \Phroses\DB.
 - New /admin/info page displays versioning information.
 - Phroses is now a Debian package.  Packages for other systems to be added in future versions.
 - Since you can update/upgrade through apt now, the web interface for upgrading has been removed.
 - New Plugin class for loading and interfacing with plugins.
 - Command parsing & handling moved to \Phroses\Commands namespace
 - Mode handling moved to \Phroses\Modes namespace
 - New Sanitizer class to apply sanitization callbacks to array elements
<br />

---

<br />

## v0.7.1 
- JavaScript now gets minified on each build (rather than relying on IDE functionality).  Replaced main javascript file in production with a newer version.
- Fixes an issue where cli commands were not executing properly
- Fixes an issue where the updater would not work if using a custom admin uri


## v0.7.0
### New Features
- **Fix Incomplete Redirects**: you can now fix incomplete redirects on-page.  A more detailed error is displayed as well.
- **Configurable Admin URI**: you can now configure /admin and subpages to have a different base uri
- **Site Maintenance Mode**: you can now turn on maintenance mode for specific sites rather than all of them
- **Site Renaming**: you can now rename the site from the dashboard
- **Change Site URL**: you can now change the url of the website from the dashboard.  
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
- Phroses\JsonOutput, Phroses\JsonOutputError, Phroses\sendEvent and Phroses\mapValue have been removed
- Phroses\mapError added (shorthand for outputting a json error if an expression evaluates to true)
- Phroses\allKeysExist, Phroses\allKeysDontExist, Phroses\safeArrayValEquals array utilities added

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







